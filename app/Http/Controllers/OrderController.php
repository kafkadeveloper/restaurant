<?php

namespace App\Http\Controllers;

use App\Category;
use App\Food;
use App\Order;
use App\Table;

class OrderController extends Controller
{
    public function create(Table $table)
    {
        if ($table->isFree == 0) {
            $order = new Order;
            $order->table_id = $table->id;
            $order->user_id = \Auth::user()->id;
            $order->save();

            $categories = Category::orderBy('id', 'asc')->get();

            return view('order', [
                'table' => $table,
                'categories' => $categories,
                'order' => $order
            ]);
        } else {
            echo "Не может быть больше оного заказа!!!";
        }
    }

    public function update(Table $table, Order $order)
    {
        $categories = Category::orderBy('id', 'asc')->get();

        return view('order_upd', [
            'categories' => $categories,
            'table' => $table,
            'order' => $order
        ]);
    }

    public function info(Table $table)
    {
        return view('/tableinfo', [
            'table' => $table
        ]);
    }

    public function addFood(Table $table, Order $order, Food $food)
    {
        $order->foods()->attach($food->id);

        return redirect('/waiter/table/' . $table->id . '/order/' . $order->id);
    }

    public function confirm(Table $table, Order $order)
    {
        foreach ($order->foods as $food) {
            if ($food->pivot->confirmed == 0) {
                $food->orders()->updateExistingPivot($order->id, ['confirmed' => 1, 'dateTimeInCook' => date('Y-m-d H:i:s')]);
            }
        }

        $order->price = $order->totalPrice();
        $order->save();

        return redirect('/waiter/table/' . $table->id . '/order/' . $order->id);
    }

    public function deleteFood(Table $table, Order $order, Food $food, $created_at)
    {

        $food->orders()->wherePivot('created_at', '=', $created_at)->newPivotStatementForId($order->id)->where('confirmed', '=', 0)->delete();
//        $food->orders()->detach($order->id);

        return redirect('/waiter/table/' . $table->id . '/order/' . $order->id);
    }

    public function closeOrder(Table $table, Order $order, Food $food)
    {
        $order->delete();

        return redirect('/waiter/hall');
    }
}
