<?php

/** Import excel file into database 
 * 
 * 
 * Excel file import functionilty made in laravel i use packages of `maatwebsite` to import large file size data.
 * I am apply lazycollection it import data in a chunk of 10,000.
 * 
 * When i am implementing an import I saw there is difference bettween acedemic year and season year.
*/




/**
 * STEP 1
 * This is the first step to insert data into branch table
 */
    $specificColumns = $rows->map(function ($row) {
        return [
            'faculty' => $row['faculty'],
        ];
    });
    LazyCollection::make(function () use ($specificColumns) {
        foreach ($specificColumns as $item) {
            yield $item;
        }
    })
    ->chunk(10000) //split in chunk to reduce the number of queries
    ->each(function ($item) {
        $line = json_decode($item, true);
        foreach ($line as $sline) {
            /** 
             * Step 1 to insert data first in branch.
             * Insert branch data */
            $branchCount = Branch::where('branch_name' , $sline['faculty'])->count();
            if($branchCount == 0){
                $branch = new Branch;
                $branch->branch_name = $sline['faculty'];
                $branch->save();
            }
        }
    });

/**
 * Fetch all the id's in array form from branch table
 */
    $getAllBranchData = Branch::pluck('id')->toArray();
/** 
 * Step 2 to insert data in fee categories.
 * Insert fee_categories data */
    $feeCategory = ['General','NON SAARC NRI','SAARC NRI'];
    foreach ($feeCategory as $type) {
        foreach ($getAllBranchData as $id) {
            $model = new FeeCategory();
            $model->branchID = $id;
            $model->fee_category = $type;
            $model->save();
        }
    }
/** 
 * Step 3 to insert data in fee collection types.
 * Insert fee_collection_types data */
    $collectionType = ['Academic','Academic Misc','Hostel','Hostel Misc','Transport','Transport Misc'];
    foreach ($collectionType as $type) {
        foreach ($getAllBranchData as $id) {
            $model = new FeeCollectionType();
            $model->branchID = $id;
            $model->collectionhead = $type;
            $model->save();
        }
    }
/** 
 * Step 4 to insert data in fee types.
 * Insert fee_types data */
    $file = $request->file('file');
    $tempPath = $file->storeAs('temp', $file->getClientOriginalName());
    $rows = Excel::toCollection(null, $tempPath)->flatten(1);
    $specificColumns = $rows->pluck(16);
    $formattedArray = [];
    foreach ($specificColumns as $value) {
        $formattedArray[] = $value;
    }
    $uniqueData  = array_unique(array_slice($formattedArray , 1), SORT_REGULAR);
    foreach ($uniqueData as $type) {
        $feeTypeCount = FeeType::where('fee_head' , $type)->count();
        if($feeTypeCount == 0){
            foreach ($getAllBranchData as $id) {
                $FeeType = new FeeType;
                $FeeType->branchID = $id;
                $FeeType->fee_head = $type;
                $FeeType->save();
            }
        }
    }
/** 
 * Step 5 to insert data in financial trans.
 * Insert financial_trans , financial_trans_details , common_fee_collections , 	common_fee_collection_headwises data */
    $specificColumns = $rows->map(function ($row) {
        return [
            'faculty' => $row['faculty'],
            'fee_head' => $row['fee_head'], 
            'voucher_type' => $row['voucher_type'],
            'roll_no' => $row['roll_no'],
            'date' => $row['date'],
            'academic_year' => $row['academic_year'],
            'due_amount' => $row['due_amount'],
            'concession_amount' => $row['concession_amount'],
            'scholarship_amount' => $row['scholarship_amount'],
            'reverse_concession_amount' => $row['reverse_concession_amount'],
            'write_off_amount' => $row['write_off_amount'],
            'receipt_no' => $row['receipt_no'],
        ];
    });
    LazyCollection::make(function () use ($specificColumns) {
        foreach ($specificColumns as $item) {
            yield $item;
        }
    })
    ->chunk(10000) //split in chunk to reduce the number of queries
    ->each(function ($item) {
        $line = json_decode($item, true);
        foreach ($line as $sline) {
            $branchData = Branch::select('id')->where('branch_name' , $sline['faculty'])->first();
            $transaction_dt = date("Y-m-d", strtotime($sline['date']));
            $cond = ['roll_number' => $sline['roll_no'] , 'academic_year' => $sline['academic_year']];

            /** Get amount for Financial Trans */
            if(
                $sline['voucher_type'] == 'DUE' || 
                $sline['voucher_type'] == 'CONCESSION' || 
                $sline['voucher_type'] == 'SCHOLARSHIP' || 
                $sline['voucher_type'] == 'REVCONCESSION' ||
                $sline['voucher_type'] == 'REVDUE'
            ){
                $amount = 0;
                if($sline['voucher_type'] == 'DUE'){
                    $amount = $sline['due_amount'];
                } else if($sline['voucher_type'] == 'CONCESSION'){
                    $amount = $sline['concession_amount'];
                } else if($sline['voucher_type'] == 'SCHOLARSHIP'){
                    $amount = $sline['scholarship_amount'];
                } else if($sline['voucher_type'] == 'REVCONCESSION'){
                    $amount = $sline['reverse_concession_amount'];
                } else if($sline['voucher_type'] == 'REVDUE'){
                    $amount = $sline['write_off_amount'];
                }

                $FinancialTransCount = FinancialTrans::where($cond)->whereDate('transaction_dt', $transaction_dt)->count();
                if($FinancialTransCount == 0){
                    $insFinanT = new FinancialTrans;
                    $insFinanT->branchID = !empty($branchData->id) ? $branchData->id : null;
                    $insFinanT->academic_year = $sline['academic_year'];
                    $insFinanT->voucher_type = $sline['voucher_type'];
                    $insFinanT->roll_number = $sline['roll_no'];
                    $insFinanT->amount = $amount;
                    $insFinanT->transaction_dt = $transaction_dt;
                    $insFinanT->save();
                    $FinancialTransData = FinancialTrans::select('id')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                    $financialTransID = $FinancialTransData->id;
                } else {
                    $FinancialTransData = FinancialTrans::select('id','amount')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                    $financialTransID = $FinancialTransData->id;
                    $insFinanT = FinancialTrans::find($financialTransID);
                    $insFinanT->amount = $amount + $FinancialTransData->amount;
                    $insFinanT->save();
                }

                $FeeTypeID = null;
                if(!empty($branchData->id)){
                    $feeTypeCount = FeeType::where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->count();
                    if($feeTypeCount > 0){
                        $feeTypeData = FeeType::select('id')->where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->first();
                        $FeeTypeID = $feeTypeData->id;
                    }
                }

                $insFinanTDetail = new FinancialTransDetail;
                $insFinanTDetail->branchID = !empty($branchData->id) ? $branchData->id : null;
                $insFinanTDetail->headID  = $FeeTypeID;
                $insFinanTDetail->financialTransID  = $financialTransID;
                $insFinanTDetail->academic_year  = $sline['academic_year'];
                $insFinanTDetail->voucher_type  = $sline['voucher_type'];
                $insFinanTDetail->amount  = $amount;
                $insFinanTDetail->receipt_number  = $sline['receipt_no'];
                $insFinanTDetail->transaction_dt  = $transaction_dt;
                $insFinanTDetail->save();
            }

            /** Get amount for Common Fee Collection */
            if(
                $sline['voucher_type'] == 'RCPT' || 
                $sline['voucher_type'] == 'REVRCPT' || 
                $sline['voucher_type'] == 'JV' || 
                $sline['voucher_type'] == 'REVJV' ||
                $sline['voucher_type'] == 'PMT' ||
                $sline['voucher_type'] == 'REVPMT'
            ){
                $amount = 0;
                $statusInactive = '';
                if($sline['voucher_type'] == 'RCPT'){
                    $amount = $sline['paid_amount'];
                    $statusInactive = 0;
                } else if($sline['voucher_type'] == 'REVRCPT'){
                    $amount = $sline['paid_amount'];
                    $statusInactive = 1;
                } else if($sline['voucher_type'] == 'JV'){
                    $amount = $sline['adjusted_amount'];
                    $statusInactive = 0;
                } else if($sline['voucher_type'] == 'REVJV'){
                    $amount = $sline['adjusted_amount'];
                    $statusInactive = 1;
                } else if($sline['voucher_type'] == 'PMT'){
                    $amount = $sline['refund_amount'];
                    $statusInactive = 0;
                } else if($sline['voucher_type'] == 'REVPMT'){
                    $amount = $sline['refund_amount'];
                    $statusInactive = 1;
                } else if($sline['voucher_type'] == 'FUNDTRANSFER'){
                    $amount = $sline['fund_transfer_amount'];
                    $statusInactive = null;
                }

                $CommonFeeCollectionCount = CommonFeeCollection::where($cond)->whereDate('transaction_dt', $transaction_dt)->count();
                if($CommonFeeCollectionCount == 0){
                    $insCommFeeColl = new CommonFeeCollection;
                    $insCommFeeColl->branchID = !empty($branchData->id) ? $branchData->id : null;
                    $insCommFeeColl->academic_year = $sline['academic_year'];
                    $insCommFeeColl->voucher_type = $sline['voucher_type'];
                    $insCommFeeColl->roll_number = $sline['roll_no'];
                    $insCommFeeColl->amount = $amount;
                    $insCommFeeColl->transaction_dt = $transaction_dt;
                    $insCommFeeColl->inactive = $statusInactive;
                    $insCommFeeColl->save();
                    $CommonFeeCollectionData = CommonFeeCollection::select('id')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                    $CommonFeeCollectionID = $CommonFeeCollectionData->id;
                } else {
                    $CommonFeeCollectionData = CommonFeeCollection::where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                    $CommonFeeCollectionID = $CommonFeeCollectionData->id;
                    $insFeeColl = CommonFeeCollection::find($CommonFeeCollectionID);
                    $insFeeColl->amount = $amount + $CommonFeeCollectionData->amount;
                    $insFeeColl->save();
                }

                $FeeTypeID = null;
                if(!empty($branchData->id)){
                    $feeTypeCount = FeeType::where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->count();
                    if($feeTypeCount > 0){
                        $feeTypeData = FeeType::select('id')->where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->first();
                        $FeeTypeID = $feeTypeData->id;
                    }
                }

                $insFinanTDetail = new CommonFeeCollectionHeadwise;
                $insFinanTDetail->branchID = !empty($branchData->id) ? $branchData->id : null;
                $insFinanTDetail->headID  = $FeeTypeID;
                $insFinanTDetail->commonFeeCollectionID  = $CommonFeeCollectionID;
                $insFinanTDetail->academic_year  = $sline['academic_year'];
                $insFinanTDetail->voucher_type  = $sline['voucher_type'];
                $insFinanTDetail->amount  = $amount;
                $insFinanTDetail->receipt_number  = $sline['receipt_no'];
                $insFinanTDetail->transaction_dt  = $transaction_dt;
                $insFinanTDetail->save();
            }
        }
    });