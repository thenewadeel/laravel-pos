<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Livewire\Component;

class ItemSearch extends Component
{
    public $order;
    public $shop;
    public $products;
    public $searchText; // = 'asd';
    public $searching = false;
    public function mount(Order $order = null)
    {
        $this->order = $order ? $order : Order::first();
        $this->shop = ($order->shop != null) ? $order->shop : null;
        $this->searchText = '';
    }
    public function search()
    {
        $this->searching = true;
        // $prods = $this->getProducts();
        $this->products = Product::where('name', 'like', '%' . $this->searchText . '%')->get();
        // $this->searching = false;
    }
    public function getProducts()
    {
        return $this->shop ?  $this->shop->products() : collect([]);
    }
    public function resetSearch()
    {
        $this->searchText = '';
        $this->products = collect([]);
        $this->searching = false;
    }
    public function render()
    {
        return view('livewire.item-search');
    }
}
