<?php

namespace App\Http\Controllers;

use App\Category;
use App\Food;
use App\FoodPrice;
use App\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function index()
    {
        $foods = Food::orderBy('id', 'asc')->get();
        $categories = Category::orderBy('id', 'asc')->get();

        return view('food', [
            'foods' => $foods,
            'categories' => $categories
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:30',
        ]);

        if ($validator->fails()) {
            return redirect('/food')
                ->withInput()
                ->withErrors($validator);
        }

        $food = new Food;
        $food->name = $request->name;
        $food->category_id = $request->category_id;
        $food->save();

        $food_price = new FoodPrice;
        $food_price->food_id = $food->id;
        $food_price->save();

        return redirect('/food');
    }

    public function edit(Food $food)
    {
        $categories = Category::orderBy('id', 'asc')->get();

        return view('foodupd', [
            'food' => $food,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Food $food)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:30',
        ]);

        if ($validator->fails()) {
            return redirect('/foodupd/' . $food->id)
                ->withInput()
                ->withErrors($validator);
        }

        $food->name = $request->name;
        $food->category_id = $request->category_id;
        $food->save();

        return redirect('/food');
    }

    public function delete(Food $food)
    {
        $food->ingredients()->detach();
        $food->delete();

        return redirect('/food');
    }

    public function content(Food $food)
    {

        $ingredients = $food->ingredients;
        $cost_price = 0;
        $total_weight = 0;
        foreach ($ingredients as $ingredient) {
            $mass = $ingredient->pivot->mass;
            $price = $ingredient->prices->sortByDesc('dateTime')->first()->price;
            $cost_price += $mass * $price / 100;
            $total_weight += $mass;
        }

        $food->mass = $total_weight;
        $food->save();

        $allIngredients = Ingredient::get();

        return view('/content', [
            'food' => $food,
            'ingredients' => $ingredients,
            'allIngredients' => $allIngredients,
            'cost_price' => $cost_price,
            'total_weight' => $total_weight
        ]);
    }

    public function addIngredient(Food $food, Ingredient $oneIngredient, Request $request)
    {
        $food->ingredients()->attach($oneIngredient->id, ["mass" => $request->mass]);

        return redirect('/food/' . $food->id . '/content');
    }

    public function delIngredient(Food $food, Ingredient $ingredient)
    {
        $ingredient->foods()->detach($food->id);

        return redirect('/food/' . $food->id . '/content');
    }

    public function setPrice(Food $food, Request $request)
    {

        $ingredients = $food->ingredients;
        $cost_price = 0;
        foreach ($ingredients as $ingredient) {
            $mass = $ingredient->pivot->mass;
            $price = $ingredient->prices->sortByDesc('dateTime')->first()->price;
            $cost_price += $mass * $price / 100;
        }

        FoodPrice::where('food_id', $food->id)->delete();
        $food_price = new FoodPrice;
        $food_price->food_id = $food->id;
        $food_price->netCost = $cost_price;
        $food_price->price = $request->price;
        $food_price->save();

        return redirect('/food/' . $food->id . '/content');

    }

    public function history(Food $food)
    {
        $prices = FoodPrice::withTrashed()->where('food_id', $food->id)->get();
//        dd($prices);
        foreach($prices as $price) {
            echo 'C ' . $price->created_at . ' по ' . $price->deleted_at . ' стоимость: ' . $price->price . ' , себестоимость ингредиентов: ' . $price->netCost . "<br>";
        }
    }
}
