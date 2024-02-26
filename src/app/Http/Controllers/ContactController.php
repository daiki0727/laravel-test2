<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Contact;
use Illuminate\Http\Request;



class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('contact', compact('categories'));
    }

    public function confirm(ContactRequest $request)
    {
        $contacts = $request->all();
        $category = Category::find($request->category_id);
        return view('confirm', compact('contacts', 'category'));
    }

    public function store(ContactRequest $request)
    {
        if ($request->has('back')) {
            return redirect('/')->withInput();
        }

        $request['tell'] = $request->tel_1 . $request->tel_2 . $request->tel_3;
        Contact::create(
            $request->only([
                'category_id',
                'first_name',
                'last_name',
                'gender',
                'email',
                'tell',
                'address',
                'building',
                'detail'
            ])
        );

        return view('thanks');
    }

    public function admin()
    {
        $contacts = Contact::with('category')->paginate(10);
        $categories = Category::all();
        // Categoryクラスのallメソッドを利用し、categoriesテーブルから全権取得 //
        return view('admin', compact('contacts', 'categories'));
        // conpact関数で$contactsと$categoriesを収納したadminを表示 //
    }

    public function search(Request $request)
    {
        if ($request->has('reset')) {
            return redirect('/admin')->withInput();
        }
        $query = Contact::query();
        $query = $this->getSearchQuery($request, $query);
        $contacts = $query->paginate(10);
        $categories = Category::all();
        $csvData = $query->get();
        return view('admin', compact('contacts', 'categories', 'csvData'));
    }
    /*　
    public function search(Request $request)
    {
        if ($request->has('reset')) {
            return redirect('/admin')->withInput();
        }　//リセットボタンを押された際に検索条件をリセット//
        $query = Contact::query();
        //Eloquentモデルのクエリビルダーを取得するためのメソッド//

        $query = $this->getSearchQuery($request, $query);
        //リクエストから検索条件を取得し、それらの条件をもとにクエリを構築するために使用//

        $contacts = $query->paginate(10);
        //$queryには検索条件が適用されたクエリビルダーが格納されている。そして、paginate(10)メソッドが呼び出されています。これにより、そのクエリの結果が10件ずつページングされ、ページごとのデータが取得される。そして、結果が$contacts変数に格納される。//
        $csvData = $query->get();
        // $query->get();はLaravelのEloquentクエリビルダーまたはクエリビルダーインスタンスを使用して
        　　データベースからデータを取得するためのメソッド　//
        $categories = Category::all();
        return view('admin', compact('contacts', 'categories', 'csvData'));
    } 
    */
    private function getSearchQuery($request, $query)
    {
        $query = Contact::query();

        if (!empty($request->keyword)) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->keyword . '%')
                    ->orwhere('last_name', 'like', '%' . $request->keyword . '%')
                    ->orwhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        if (!empty($request->gender)) {
            $query->where('gender', '=', $request->gender);
        }

        if (!empty($request->category_id)) {
            $query->where('category_id', '=',  $request->category_id);
        }

        if (!empty($request->date)) {
            $query->whereDate('created_at', '=', $request->date);
        }

        return $query;
    }
    /*  !empty()はもしも()が空でない場合に検索処理実行
        '='は比較演算子。指定されたカラム（ここではgender）の値が指定された値（$request->gender）と等しい行を選択するようになる。
    private function getSearchQuery($request, $query)
    {
        if(!empty($request->keyword)) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        if (!empty($request->gender)) {
            $query->where('gender', '=', $request->gender);
        }

        if (!empty($request->category_id)) {
            $query->where('category_id', '=', $request->category_id);
        }

        if (!empty($request->date)) {
            $query->whereDate('created_at', '=', $request->date);
        }

        return $query;
    }
    */
    public function destroy(Request $request)
    {
        Contact::find($request->id)->delete();
        return redirect('/admin');
    }
}
