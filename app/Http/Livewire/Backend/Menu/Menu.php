<?php

namespace App\Http\Livewire\Backend\Menu;

use App\Models\Menu as ModelsMenu;
use Livewire\Component;

class Menu extends Component {

    public $menus, $addMenuItem, $updateMenuItem, $menu, $show_parent;

    public function mount() {
        $this->clearMenuObject();
        $this->addMenuItem = false;
        $this->updateMenuItem = false;
        $this->updateMenus();
    }

    public function updateMenus() {
        $this->menus = ModelsMenu::where('parent_id', null)->orderBy('order')->get();
    }

    public function moveUp($menu) {
        $menu = ModelsMenu::findOrFail($menu['id']);
        $swap = ModelsMenu::where('order', '<', $menu->order)->where('parent_id', $menu->parent_id)->orderBy('order', 'desc')->first();
        if ($swap) {
            $order = $menu->order;
            $menu->order = $swap->order;
            $swap->order = $order;
            $swap->save();
            $menu->save();
            $this->updateMenus();
        }
    }

    public function moveDown($menu) {
        $menu = ModelsMenu::findOrFail($menu['id']);
        $swap = ModelsMenu::where('order', '>', $menu->order)->where('parent_id', $menu->parent_id)->orderBy('order', 'asc')->first();
        if ($swap) {
            $order = $menu->order;
            $menu->order = $swap->order;
            $swap->order = $order;
            $swap->save();
            $menu->save();
            $this->updateMenus();
        }
    }

    public function toggleStatus($menu) {
        $menu = ModelsMenu::findOrFail($menu['id']);
        $menu->status = !$menu->status;
        $menu->save();
        $childs = $menu->getChildAll();
        if (count($childs) > 0) {
            foreach ($childs as $child) {
                $child->status = $menu->status;
                $child->save();
            }
        }
        $this->updateMenus();
    }

    public function clearMenuObject() {
        $this->menu = [
            'name' => '',
            'link' => '',
            'target' => '',
            'parent_id' => null,
        ];
        $this->show_parent = true;
    }

    public function clearAddUpdate() {
        $this->addMenuItem = false;
        $this->updateMenuItem = false;
        $this->updateMenus();
        $this->clearMenuObject();
    }

    public function add() {
        $this->validate(
            [
                'menu.name' => 'required',
                'menu.link' => 'required',
            ],
            [
                'menu.name.required' => 'Menu Name is Required',
                'menu.link.required' => 'Menu Link is Required'
            ]
        );
        $this->menu['target'] = $this->menu['target'] ? '_blank' : '_self';
        if ($this->menu['parent_id'] == 0) {
            $this->menu['parent_id'] = null;
        }
        $order = ModelsMenu::select('order')->where('parent_id', $this->menu['parent_id'])->orderBy('order', 'desc')->first();
        $this->menu['order'] = (($order) ? $order->order : 0) + 1;
        ModelsMenu::create($this->menu);
        $this->emit('saved');
        $this->updateMenus();
        $this->clearAddUpdate();
    }

    public function showUpdate($menu) {
        $this->updateMenuItem = true;
        $this->menu = $menu;
        $this->menu['target'] = $this->menu['target'] === '_self' ? 0 : 1;
        if (ModelsMenu::where('parent_id', $menu['id'])->count() > 0) {
            $this->show_parent = false;
        }
    }

    public function update() {
        $this->validate(
            [
                'menu.name' => 'required',
                'menu.link' => 'required',
            ],
            [
                'menu.name.required' => 'Menu Name is Required',
                'menu.link.required' => 'Menu Link is Required'
            ]
        );
        $menu = ModelsMenu::findOrFail($this->menu['id']);
        if ($this->menu['parent_id'] == 0) {
            $this->menu['parent_id'] = null;
        }
        if ($menu->parent_id != $this->menu['parent_id']) {
            $order = ModelsMenu::select('order')->where('parent_id', $this->menu['parent_id'])->orderBy('order', 'desc')->first();
            $this->menu['order'] = (($order) ? $order->order : 0) + 1;
        }
        $this->menu['target'] = $this->menu['target'] ? '_blank' : '_self';
        $menu->fill($this->menu);
        $menu->save();
        $this->menu['target'] = $this->menu['target'] === '_self' ? 0 : 1;
        $this->emit('saved');
    }

    public function delete($menu) {
        $menu = ModelsMenu::findOrFail($menu['id']);
        $childs = $menu->getChildAll();
        if (count($childs) > 0) {
            $order = ModelsMenu::select('order')->where('parent_id', null)->orderBy('order', 'desc')->first();
            $next = (($order) ? $order->order : 0) + 1;
            foreach ($childs as $child) {
                $child->order = $next;
                $child->parent_id = null;
                $child->save();
                $next = $next + 1;
            }
        }
        $menu->delete();
        $this->updateMenus();
    }

    public function render() {
        return view('backend.menu.menu');
    }
}
