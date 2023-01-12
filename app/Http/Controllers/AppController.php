<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Page;
use App\Models\TMail;
use Carbon\Carbon;
use Ddeboer\Imap\Search\Date\Before;
use Illuminate\Support\Facades\Artisan;

class AppController extends Controller {

    public function load() {
        if (file_exists(public_path('themes')) !== TRUE) {
            symlink(base_path('resources/views/themes'), public_path('themes'));
        }
        if (file_exists(public_path('storage')) !== TRUE) {
            Artisan::call('storage:link');
        }
        $homepage = config('app.settings.homepage');
        if ($homepage == 0) {
            if (config('app.settings.disable_mailbox_slug')) {
                return $this->app();
            }
            TMail::getEmail(true);
            return redirect()->route('mailbox');
        } else {
            $page = $this->getTranslatedPage($homepage);
            $page = $this->setHeaders($page);
            return view('themes.' . config('app.settings.theme') . '.app')->with(compact('page'));
        }
    }

    public function mailbox($email = null) {
        if ($email) {
            if (config('app.settings.enable_create_from_url')) {
                TMail::createCustomEmailFull($email);
            }
            return redirect()->route('mailbox');
        }
        if (config('app.settings.homepage') && !TMail::getEmail()) {
            return redirect()->route('home');
        }
        if (config('app.settings.disable_mailbox_slug')) {
            return redirect()->route('home');
        }
        return $this->app();
    }

    public function app() {
        if (config('app.settings.theme') == 'groot' && config('app.settings.groot_theme_options.extra_text_page')) {
            $in_page = $this->getTranslatedPage(config('app.settings.groot_theme_options.extra_text_page'));
            return view('themes.' . config('app.settings.theme') . '.app')->with(compact('in_page'));
        }
        return view('themes.' . config('app.settings.theme') . '.app');
    }

    public function page($slug = '', $inner = '') {
        if ($inner) {
            $parent = Page::select('id')->where('slug', $slug)->first();
            if ($parent) {
                $parent_id = $parent->id;
            } else {
                return abort(404);
            }
        }
        $lang = null;
        if (config('app.settings.lang') != session('locale')) {
            $lang = session('locale');
        }
        $page = Page::where('slug', ($inner) ? $inner : $slug)->where('parent_id', isset($parent_id) ? $parent_id : null)->where('lang', $lang)->first();
        if (!$page) {
            $page = Page::where('slug', ($inner) ? $inner : $slug)->where('parent_id', isset($parent_id) ? $parent_id : null)->where('lang', null)->first();
        }
        if ($page) {
            $page = $this->setHeaders($page);
            return view('themes.' . config('app.settings.theme') . '.app')->with(compact('page'));
        }
        return abort(404);
    }

    public function switch($email) {
        TMail::setEmail($email);
        if (config('app.settings.disable_mailbox_slug')) {
            return redirect()->route('home');
        }
        return redirect()->route('mailbox');
    }

    public function locale($locale) {
        if (in_array($locale, config('app.locales'))) {
            session(['locale' => $locale]);
            return redirect()->back();
        }
        abort(400);
    }

    public function cron($password) {
        if ($password == config('app.settings.cron_password')) {
            $this->deleteMessages();
            $this->deleteLogs();
        } else {
            return abort(401);
        }
    }

    private function getTranslatedPage($page_id) {
        $page = Page::find($page_id);
        if ($page) {
            $lang = null;
            if (config('app.settings.lang') != session('locale')) {
                $lang = session('locale');
            }
            $translated = Page::where('slug', $page->slug)->where('lang', $lang)->first();
            if ($translated) {
                return $translated;
            }
            return $page;
        }
        return false;
    }

    private function setHeaders($page) {
        $header = $page->header;
        foreach ($page->meta ? unserialize($page->meta) : [] as $meta) {
            if ($meta['name'] == 'canonical') {
                $header .= '<link rel="canonical" href="' . $meta['content'] . '" />';
            } else if (str_contains($meta['name'], 'og:')) {
                $header .= '<meta property="' . $meta['name'] . '" content="' . $meta['content'] . '" />';
            } else {
                $header .= '<meta name="' . $meta['name'] . '" content="' . $meta['content'] . '" />';
            }
        }
        $page->header = $header;
        return $page;
    }

    private function deleteMessages() {
        $before = null;
        if (config('app.settings.delete.key') == 'd') {
            $before = Carbon::now()->subDays(config('app.settings.delete.value'));
        } else if (config('app.settings.delete.key') == 'w') {
            $before = Carbon::now()->subWeeks(config('app.settings.delete.value'));
        } else {
            $before = Carbon::now()->subMonths(config('app.settings.delete.value'));
        }
        $limit = 50;
        $today = new \DateTimeImmutable($before);
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $messages = $mailbox->getMessages(new Before($today));
        $count = 0;
        foreach ($messages as $message) {
            $message->delete();
            $count++;
            if ($count >= $limit) {
                break;
            }
        }
        $connection->expunge();
        $directory = './tmp/attachments/';
        $this->rrmdir($directory);
        return "Deleted " . $count . " Messages";
    }

    private function deleteLogs() {
        Log::where('created_at', '<', Carbon::now()->subWeek())->delete();
    }

    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }
}
