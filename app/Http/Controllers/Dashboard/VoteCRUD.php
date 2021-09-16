<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vote;
use App\Models\UserVote;
use App\Models\VoteLink;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VoteCRUD extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $lookup = array();
        $user = Auth::user();
        $items = Vote::all();
        $result = array();
        foreach ($items as $item) {
            $name = $item->votename;
            $vote = $item->id_vote;
            $jumlahOpsi = Vote::select("kandidat")->where("id_vote", $vote)->get();
            $creator = $item->creator;
            if (!array_key_exists($name, $lookup) && !array_key_exists($vote, $lookup) && $creator == $user->email) {
                $lookup[$name] = 1;
                $lookup[$vote] = 1;
                $VoteResults = UserVote::select("candidate", "email")->where("id_vote", $vote)->get();
                // Log::channel('stderr')->info($VoteResults);
                $jumlahVoters = [];
                $jumlahkandidat = [];
                foreach ($VoteResults as $el) {
                    $jumlahkandidat[$el->candidate] = (isset($jumlahkandidat[$el->candidate]) ? $jumlahkandidat[$el->candidate] : 0) + 1;
                    array_push($jumlahVoters, [
                        'email' => $el->email,
                        'pilih' => $el->candidate,
                    ]);
                }

                array_push($result, [
                    "id_vote" => $vote,
                    "name" => $name,
                    "jumlahkandidat" => $jumlahkandidat,
                    "jumlahVoters" => $jumlahVoters,
                    "jumlahOpsi"=>count($jumlahOpsi)
                ]);
            }
        }
        return response()->json([
            "votes" => $result
        ], 200);
        // Log::channel('stderr')->info($items);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $req = $request->all();
        // Log::channel('stderr')->info($req);

        $validatedData = Validator::make(
            $req,
            [
                'kandidatImage' => 'required|png,jpg|max:2048',
            ]
        );
        // $files = [];
        if ($request->hasfile('kandidatImage')) {
            // $path = Storage::putFile('kandidatImage', $request->file('public'));
            foreach ($request->file('kandidatImage') as $file) {
                // $name = time().rand(1,100).'.'.$file->extension();
                $filena = $file->getClientOriginalName();
                // $fileName = pathinfo($file1,PATHINFO_FILENAME);
                Storage::putFileAs('public', $file, $filena);
                // $file->move(public_path('public'), $file1);
                // $files[] = $file1;
            }
        }
        // $file= new File();
        //  $file->filenames = $files;
        //  $file->save();
        $user = Auth::user();
        $id = Str::random(6);
        $results = Vote::find($id);
        if ($results == null) {
            foreach ($req['fileName'] as $index => $element) {
                $vote = new Vote();
                $vote->id_vote = $id;
                $vote->creator = $user->email;
                $vote->votename = $req['votename'];
                $vote->kandidat = $req['kandidat'][$index];
                $vote->kandidatImage = $element;
                $vote->save();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $req = $request->all();
        $id_vote = $req['id_vote'];
        $kandidat = $req['kandidat'];
        $user = Auth::user();
        $check = UserVote::select("email")->where("id_vote", $id_vote)->where("email", $user->email)->get();
        if (count($check) == 0) {
            $UV = new UserVote();
            $UV->email = $user->email;
            $UV->id_vote = $id_vote;
            $UV->candidate = $kandidat;
            $UV->save();
            return response()->json("berhasil");
        } else {
            return response()->json("gagal");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {

        // Log::channel('stderr')->info($id);
        $items = Vote::select("id", "id_vote", "votename", "kandidat", 'kandidatImage')->where("id_vote", $id)->get();

        $VoteResults = UserVote::select("candidate", "email")->where("id_vote", $items[0]->id_vote)->get();
        // Log::channel('stderr')->info($VoteResults);
        $jumlahVoters = [];
        $jumlahkandidat = [];
        foreach ($VoteResults as $el) {
            $jumlahkandidat[$el->candidate] = (isset($jumlahkandidat[$el->candidate]) ? $jumlahkandidat[$el->candidate] : 0) + 1;
            array_push($jumlahVoters, [
                'email' => $el->email,
                'pilih' => $el->candidate,
            ]);
        }
        return response()->json([
            "vote" => $items,
            "result" => [
                "jumlahkandidat" => $jumlahkandidat,
                "jumlahVoters" => $jumlahVoters,
            ]
        ]);
    }

    public function indexUrl(Request $request)
    {
        $req = $request->only(['code']);
        $id = $req['code'];

        $lookup = array();
        $user = Auth::user();
        $items = VoteLink::select("id", "id_vote", "votename")->where("id_url", $id)->get();
        foreach ($items as $item) {
            $vote = $item->id_vote;
            $jumlahOpsi = Vote::select("kandidat")->where("id_vote", $vote)->get();
            $item->jumlahOpsi=count($jumlahOpsi);

            $VoteResults = UserVote::select("candidate", "email")->where("id_vote", $vote)->get();
            // Log::channel('stderr')->info($VoteResults);
            $jumlahVoters = [];
            $jumlahkandidat = [];
            $voterCanVote = null;
            foreach ($VoteResults as $el) {
                $jumlahkandidat[$el->candidate] = (isset($jumlahkandidat[$el->candidate]) ? $jumlahkandidat[$el->candidate] : 0) + 1;
                array_push($jumlahVoters, [
                    'email' => $el->email,
                    'pilih' => $el->candidate,
                ]);
            }
            foreach ($VoteResults as $el) {
           if ($el->email==$user->email){
               $voterCanVote=$el->candidate;
                break;
        }else $voterCanVote=null;
            }
            $item["jumlahkandidat"]=$jumlahkandidat;
            $item["jumlahVoters"]=$jumlahVoters;
            $item["voterCanVote"]=$voterCanVote;
            Log::channel('stderr')->info($lookup);
        }
        Log::channel('stderr')->info($items);
        return response()->json($items);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if ($request->hasfile('kandidatImage')) {
            // $path = Storage::putFile('kandidatImage', $request->file('public'));
            foreach ($request->file('kandidatImage') as $file) {
                $filena = explode(".", $file->getClientOriginalName());
                // Log::channel('stderr')->info($filena[0]);
                $filena = Vote::select("kandidatImage")->where("kandidatImage", 'LIKE', "%" . $filena[0] . "%")->get();
                // Log::channel('stderr')->info($filena);
                if (count($filena) > 0) {
                    $exists = Storage::disk('public')->exists($filena[0]["kandidatImage"]);
                    if ($exists) {
                        Storage::disk('public')->delete($filena[0]["kandidatImage"]);
                    }
                }
                Storage::putFileAs('public', $file, $file->getClientOriginalName());
                // if (count($filena)>0) {
                //     $exists = Storage::disk('public')->exists($filena[0]["kandidatImage"]);
                //     if($exists){
                //         Storage::disk('public')->delete($filena[0]["kandidatImage"]);
                //     }
                // // $name = time().rand(1,100).'.'.$file->extension();
                // // $fileName = pathinfo($file1,PATHINFO_FILENAME);
                // Storage::putFileAs('public', $file ,$file->getClientOriginalName());
                // // $file->move(public_path('public'), $file1);
                // }
                // $files[] = $file1;
            }
        }
        $req = $request->all();
        // $req = $req['Vote'];
        // $array = (array) $req;
        Log::channel('stderr')->info($req);

        $user = Auth::user();
        foreach ($req['action'] as $key => $element) {
            if ($element == "tambah") {
                $vote = new Vote();
                $vote->id_vote = $req['id_vote'][$key];
                $vote->creator = $user->email;
                $vote->votename = $req['votename'][$key];
                $vote->kandidat = $req['kandidat'][$key];
                $vote->kandidatImage = $req['fileName'][$key];
                $vote->save();
            } elseif ($element == "ubah") {
                $vote = Vote::where("id", $req['id'][$key])->update([
                    'votename' => $req['votename'][$key],
                    'kandidat' => $req['kandidat'][$key],
                    'kandidatImage' => $req['fileName'][$key],
                ]);
            } else {

                $filena = Vote::select("kandidatImage")->where("id", $req['id'][$key])->get();
                $exists = Storage::disk('public')->exists($filena[0]["kandidatImage"]);
                if ($exists) {
                    Storage::disk('public')->delete($filena[0]["kandidatImage"]);
                }
                $vote = Vote::find($req['id'][$key]);
                if ($vote->creator == $user->email) {
                    $vote->delete();
                }
                UserVote::where("id_vote", $req['id_vote'][$key])->where("candidate", $req['kandidat'][$key])->delete();
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
    public function edit(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $filena = Vote::select("kandidatImage")->where("id_vote", $id)->get();
        foreach ($filena as $file) {

            $exists = Storage::disk('public')->exists($file["kandidatImage"]);
            if ($exists) {
                Storage::disk('public')->delete($file["kandidatImage"]);
            }
        }
        $user = Auth::user();
        Vote::where("id_vote", $id)->where("creator", $user->email)->delete();
    }
    public function bulkDestroy(Request $request)
    {

        $user = Auth::user();
        $data = $request->all();

        foreach ($data as $key => $value) {

            $filena = Vote::select("kandidatImage")->where("id_vote", $value['id_vote'])->get();
            foreach ($filena as $file) {

                $exists = Storage::disk('public')->exists($file["kandidatImage"]);
                if ($exists) {
                    Storage::disk('public')->delete($file["kandidatImage"]);
                }
            }
            VoteLink::where("id_vote", $value['id_vote'])->where("email", $user->email)->delete();
            Vote::where("id_vote", $value['id_vote'])->where("creator", $user->email)->delete();
        }
    }
    public function destroyVoter(Request $request)
    {
        ['email' => $email, 'id_vote' => $id_vote] = $request->all();
        UserVote::where('id_vote', $id_vote)->where('email', $email)->delete();
    }
}
