<?php

namespace App\Http\Controllers;

use App\Category;
use App\Food;
use App\Order;
use App\Table;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function create(Table $table, Order $order)
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
            return redirect('/waiter/table/' . $table->id . '/order/' . $table->orders()->
                where('table_id', '=', $table->id)->value('id'));
        }
    }

    /*
    При создании заказа, после нажатия кнопки "Отмена" удаляет заказ полностью
    без softDelete.Например, случайно кликнули на стол.
    Возвращает вид зала
     */
    public function delete(Table $table, Order $order)
    {
        $order->forceDelete();
        return redirect('waiter/hall');
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
        if ($table->isFree == 0) {
            return redirect()->back();
        } else {
            return view('/tableinfo', [
                'table' => $table
            ]);
        }
    }

    public function addFood(Table $table, Order $order, Food $food, Request $request)
    {
        $order->foods()->attach($food->id, array('comment' => $request->comment));
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
        $order->netPrice = $order->netTotalPrice();
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
        if ($order->isFree == 0) {
            $order->delete();
            return redirect('/waiter/hall');
        } else {
            return redirect('/waiter/table/' . $table->id . '/order/' . $order->id)->with('alert', 'Нельзя закрыть заказ, пока не готовы все блюда!');
        }
    }

    public function history()
    {
        $orders = Order::withTrashed()->orderBy('created_at', 'desc')->where('created_at', '>=', Carbon::now()->startOfDay()->toDateTimeString())->get();
        $total = 0;
        $netTotal = 0;
        foreach ($orders as $order){
            $total += $order->price;
            $netTotal += $order->netPrice;
        }
        $clean = $total - $netTotal;
        return view('orders_history', [
            'orders' => $orders,
            'total' => $total,
            'netTotal' => $netTotal,
            'clean' => $clean
        ]);
    }

    public function historyOnDate(Request $request)
    {
        $orders = Order::withTrashed()->orderBy('created_at', 'desc')->where('created_at', '>=', $request->date." 00:00:00")->where('created_at', '<=', $request->date." 23:59:59")->get();
        $total = 0;
        $netTotal = 0;
        foreach ($orders as $order){
            $total += $order->price;
            $netTotal += $order->netPrice;
        }
        $clean = $total - $netTotal;
        return view('orders_history', [
            'orders' => $orders,
            'total' => $total,
            'netTotal' => $netTotal,
            'clean' => $clean
        ]);
    }

    public function historyOnWeek()
    {
        $orders = Order::withTrashed()->orderBy('created_at', 'desc')->where('created_at', '>=', Carbon::now()->startOfWeek()->toDateTimeString())->get();
        $total = 0;
        $netTotal = 0;
        foreach ($orders as $order){
            $total += $order->price;
            $netTotal += $order->netPrice;
        }
        $clean = $total - $netTotal;
        return view('orders_history', [
            'orders' => $orders,
            'total' => $total,
            'netTotal' => $netTotal,
            'clean' => $clean
        ]);
    }

    public function historyAll()
    {
        $orders = Order::withTrashed()->orderBy('created_at', 'desc')->get();
        $total = 0;
        $netTotal = 0;
        foreach ($orders as $order){
            $total += $order->price;
            $netTotal += $order->netPrice;
        }
        $clean = $total - $netTotal;
        return view('orders_history', [
            'orders' => $orders,
            'total' => $total,
            'netTotal' => $netTotal,
            'clean' => $clean
        ]);
    }
}
