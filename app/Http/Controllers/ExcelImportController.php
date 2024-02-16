<?php

namespace App\Http\Controllers;
use App\Imports\ExcelImport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\Request;

use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeCollectionType;
use App\Models\FeeType;
use App\Models\FinancialTrans;

class ExcelImportController extends Controller
{
    public function home()
    {
        return view('welcome');
    }
    public function excelImport(Request $request) {
        $getAllBranchData = Branch::pluck('id')->toArray();

        /** 
         * Step 2 to insert data in fee categories.
         * Insert fee_categories data */
        // $feeCategory = ['General','NON SAARC NRI','SAARC NRI'];
        // foreach ($feeCategory as $type) {
        //     foreach ($getAllBranchData as $id) {
        //         $model = new FeeCategory();
        //         $model->branchID = $id;
        //         $model->fee_category = $type;
        //         $model->save();
        //     }
        // }

        /** 
         * Step 3 to insert data in fee collection types.
         * Insert fee_collection_types data */
        // $collectionType = ['Academic','Academic Misc','Hostel','Hostel Misc','Transport','Transport Misc'];
        // foreach ($collectionType as $type) {
        //     foreach ($getAllBranchData as $id) {
        //         $model = new FeeCollectionType();
        //         $model->branchID = $id;
        //         $model->collectionhead = $type;
        //         $model->save();
        //     }
        // }

        /** 
         * Step 4 to insert data in fee types.
         * Insert fee_types data */
        // $file = $request->file('file');
        // $tempPath = $file->storeAs('temp', $file->getClientOriginalName());
        // $rows = Excel::toCollection(null, $tempPath)->flatten(1);
        // $specificColumns = $rows->pluck(16);
        // $formattedArray = [];
        // foreach ($specificColumns as $value) {
        //     $formattedArray[] = $value;
        // }
        // $uniqueData  = array_unique(array_slice($formattedArray , 1), SORT_REGULAR);
        // foreach ($uniqueData as $type) {
        //     $feeTypeCount = FeeType::where('fee_head' , $type)->count();
        //     if($feeTypeCount == 0){
        //         foreach ($getAllBranchData as $id) {
        //             $FeeType = new FeeType;
        //             $FeeType->branchID = $id;
        //             $FeeType->fee_head = $type;
        //             $FeeType->save();
        //         }
        //     }
        // }




        $file = $request->file('file');
        $import = new ExcelImport();
        Excel::import($import , $file);
        $uniqueData = $import->getUniqueData();
        return response()->json(['success' => true]);
    }
}
