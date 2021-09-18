<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\VoteLink;
use Illuminate\Support\Facades\Log;
use App\Models\Vote;

class LinkController extends Controller
{
    public function __construct($id)
    {
        $this->id_link = $id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $votes =
        $user = Auth::user();
        $lookup = array();
        $result = array();
        $items = VoteLink::all();
        foreach ($items as $item) {
            $name = $item['votename'];
            $url = $item['id_url'];
            $vote = $item['id_vote'];
            if (!array_key_exists($url, $lookup) && $item['email'] == $user->email) {
                $lookup[$url] = 1;

                array_push($result, [
                    "id_url" => $url,
                    "id_vote" => $vote,
                    "name" => $url,
                ]);
            }
        }
        return response()->json(['votes' => $result]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $data = $data['result'];
        $user = Auth::user();
        $id = Str::random(6);
        $results = VoteLink::find($id);
        if ($results == null) {
            foreach ($data as $element) {
                $vote = new VoteLink;
                $vote->id_url = $id;
                $vote->email = $user->email;
                $vote->id_vote = $element['id_vote'];
                $vote->votename = $element['name'];
                $vote->status = "private";
                $vote->save();
            }
        }

        return response()->json($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vote = VoteLink::select("id", "id_url", "id_vote", "votename")->where("id_url", $id)->get();

        return response()->json([
            'vote' => $vote
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $data = $request->all();
        $req = $data['Vote'];
        $user = Auth::user();
        foreach ($req as $element) {
            $votename = Vote::select("votename")->where("id_vote", $element['id_vote'])->get();
            if ($element['action'] == "tambah") {
                $vote = new VoteLink();
                $vote->id_url = strtolower($element['id_url']);
                $vote->id_vote = $element['id_vote'];
                $vote->email = $user->email;
                $vote->votename = $votename[0]['votename'];
                $vote->status = "private";
                $vote->save();
            } elseif ($element['action'] == "ubah") {
                $vote = VoteLink::where("id", $element['id'])->update([
                    'votename' => $votename[0]['votename'],
                    'id_url' => strtolower($element['id_url']),
                    'id_vote' => $element['id_vote'],
                ]);
            } else {
                $vote = VoteLink::find($element['id']);
                if ($vote->email == $user->email) {
                    $vote->delete();
                }
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        VoteLink::where("id_url", $id)->where("email", $user->email)->delete();
    }
    public function bulkDestroy(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        foreach ($data as $key => $value) {
            VoteLink::where("id_url", $value['id_url'])->where("email", $user->email)->delete();
        }
    }
}
