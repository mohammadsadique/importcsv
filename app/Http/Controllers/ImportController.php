<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use App\Jobs\QueueCSVProcess;
use App\Models\Branch;

class ImportController extends Controller
{
    public function index() {
        return view('index');
    }
    public function store(Request $request) {
        if($request->hasFile('file')){
            $file = $request->file('file');
            $csvData = file($file->path());
          
            $chunks = array_chunk($csvData , 1000);
            $header = [];
            $batch = Bus::batch([])->dispatch();


            foreach($chunks as $key => $chunk){
                $csvData = array_map('str_getcsv', $chunk);
                if($key === 0){
                    $header = $csvData[0];
                    unset($csvData[0]);
                }

                $sanitizedData = $this->sanitizeData($csvData);


                // Convert data to a serializable format
                // $serializableData = [
                //     'csvData' => $csvData,
                //     'header' => $header,
                // ];
                // $programNames = array_column($csvData, 0); // Assuming the program names are in the first column

                // $batch->add(new QueueCSVProcess($programNames ));
                $batch->add(new QueueCSVProcess($sanitizedData , $header));
                // $batch->add(new QueueCSVProcess($serializableData));

            }
            // return $batch->id;
            $id = $batch->id;

            // $request->session()->flash('id', $id);
            // return redirect()->route('progress', ['id'=> $id]);
		    // return redirect()->view('progress',['id' => $id]);
            return redirect()->route('progress')->with('id', $id);


        }
    }
    private function sanitizeData($data) {
        $sanitizedData = [];
        foreach ($data as $row) {
            $sanitizedRow = array_map('utf8_encode', $row);
            $sanitizedData[] = $sanitizedRow;
        }
        return $sanitizedData;
    }

    function progress() {
        return view('progress');
    }
    public function batch($id) {
        $batchId = $id;
        $data = Bus::findBatch($batchId);

        $data1 = json_encode($data, true);
        $data2 = json_decode($data1, true);
        $progress = $data2['progress'];
        $createdAt = date('F j, Y, g:i A' , strtotime($data2['createdAt']));
        $finishedAt = !empty($data2['finishedAt']) ? date('F j, Y, g:i A' , strtotime($data2['finishedAt'])) : null;
        return response()->json(['progress' => $progress , 'createdAt' => $createdAt , 'finishedAt' => $finishedAt]);
    }
}
