<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\WithEvents;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use DB;

use Illuminate\Support\LazyCollection;
use App\Models\Branch;
use App\Models\FeeCategory;
use App\Models\FeeCollectionType;
use App\Models\FeeType;
use App\Models\FinancialTrans;
use App\Models\FinancialTransDetail;
use App\Models\CommonFeeCollection;
use App\Models\CommonFeeCollectionHeadwise;

class ExcelImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue, WithProgressBar
{
    use Importable;
    private $uniqueData;

    private $totalRows;
    private $currentRow = 0;
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        ini_set('memory_limit', '4096M');

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
                'paid_amount' => $row['paid_amount'],
                'adjusted_amount' => $row['adjusted_amount'],
                'refund_amount' => $row['refund_amount'],
                'fund_trancfer_amount' => $row['fund_trancfer_amount'],
            ];
        });
        LazyCollection::make(function () use ($specificColumns) {
            foreach ($specificColumns as $item) {
                yield $item;
            }
        })
        ->chunk(10000) //split in chunk to reduce the number of queries
        ->each(function ($item) {
            // echo $item;die;
            $line = json_decode($item, true);
            foreach ($line as $sline) {
                /** 
                 * Step 1 to insert data first in branch.
                 * Insert branch data */
                $branchCount = Branch::where('branch_name' , $sline['faculty'])->count();
                if($branchCount == 0){
                    if(!empty($sline['faculty'])){
                        $branch = new Branch;
                        $branch->branch_name = $sline['faculty'];
                        $branch->save();
                    }
                }

                $branchData = Branch::select('id')->where('branch_name' , $sline['faculty'])->first();
                $transaction_dt = date("Y-m-d", strtotime($sline['date']));
                $cond = ['roll_number' => $sline['roll_no'] , 'academic_year' => $sline['academic_year']];

                /** Get amount for Financial Trans */
                // if(
                //     $sline['voucher_type'] == 'DUE' || 
                //     $sline['voucher_type'] == 'CONCESSION' || 
                //     $sline['voucher_type'] == 'SCHOLARSHIP' || 
                //     $sline['voucher_type'] == 'REVCONCESSION' ||
                //     $sline['voucher_type'] == 'REVDUE'
                // ){

                //     $amount = 0;
                //     if($sline['voucher_type'] == 'DUE'){
                //         $amount = $sline['due_amount'];
                //     } else if($sline['voucher_type'] == 'CONCESSION'){
                //         $amount = $sline['concession_amount'];
                //     } else if($sline['voucher_type'] == 'SCHOLARSHIP'){
                //         $amount = $sline['scholarship_amount'];
                //     } else if($sline['voucher_type'] == 'REVCONCESSION'){
                //         $amount = $sline['reverse_concession_amount'];
                //     } else if($sline['voucher_type'] == 'REVDUE'){
                //         $amount = $sline['write_off_amount'];
                //     }
                   

                //     $FinancialTransCount = FinancialTrans::where($cond)->whereDate('transaction_dt', $transaction_dt)->count();
                //     if($FinancialTransCount == 0){
                //         $insFinanT = new FinancialTrans;
                //         $insFinanT->branchID = !empty($branchData->id) ? $branchData->id : null;
                //         $insFinanT->academic_year = $sline['academic_year'];
                //         $insFinanT->voucher_type = $sline['voucher_type'];
                //         $insFinanT->roll_number = $sline['roll_no'];
                //         $insFinanT->amount = $amount;
                //         $insFinanT->transaction_dt = $transaction_dt;
                //         $insFinanT->save();
                //         $FinancialTransData = FinancialTrans::select('id')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                //         $financialTransID = $FinancialTransData->id;
                //     } else {
                //         $FinancialTransData = FinancialTrans::select('id','amount')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                //         $financialTransID = $FinancialTransData->id;
                //         $insFinanT = FinancialTrans::find($financialTransID);
                //         $insFinanT->amount = $amount + $FinancialTransData->amount;
                //         $insFinanT->save();
                //     }

                //     $FeeTypeID = null;
                //     if(!empty($branchData->id)){
                //         $feeTypeCount = FeeType::where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->count();
                //         if($feeTypeCount > 0){
                //             $feeTypeData = FeeType::select('id')->where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->first();
                //             $FeeTypeID = $feeTypeData->id;
                //         }
                //     }

                //     $insFinanTDetail = new FinancialTransDetail;
                //     $insFinanTDetail->branchID = !empty($branchData->id) ? $branchData->id : null;
                //     $insFinanTDetail->headID  = $FeeTypeID;
                //     $insFinanTDetail->financialTransID  = $financialTransID;
                //     $insFinanTDetail->academic_year  = $sline['academic_year'];
                //     $insFinanTDetail->voucher_type  = $sline['voucher_type'];
                //     $insFinanTDetail->amount  = $amount;
                //     $insFinanTDetail->receipt_number  = $sline['receipt_no'];
                //     $insFinanTDetail->transaction_dt  = $transaction_dt;
                //     $insFinanTDetail->save();

                // }

                /** Get amount for Common Fee Collection */
                // if(
                //     $sline['voucher_type'] == 'RCPT' || 
                //     $sline['voucher_type'] == 'REVRCPT' || 
                //     $sline['voucher_type'] == 'JV' || 
                //     $sline['voucher_type'] == 'REVJV' ||
                //     $sline['voucher_type'] == 'PMT' ||
                //     $sline['voucher_type'] == 'REVPMT'
                // ){
                //     $amount = 0;
                //     $statusInactive = '';
                //     if($sline['voucher_type'] == 'RCPT'){
                //         $amount = $sline['paid_amount'];
                //         $statusInactive = 0;
                //     } else if($sline['voucher_type'] == 'REVRCPT'){
                //         $amount = $sline['paid_amount'];
                //         $statusInactive = 1;
                //     } else if($sline['voucher_type'] == 'JV'){
                //         $amount = $sline['adjusted_amount'];
                //         $statusInactive = 0;
                //     } else if($sline['voucher_type'] == 'REVJV'){
                //         $amount = $sline['adjusted_amount'];
                //         $statusInactive = 1;
                //     } else if($sline['voucher_type'] == 'PMT'){
                //         $amount = $sline['refund_amount'];
                //         $statusInactive = 0;
                //     } else if($sline['voucher_type'] == 'REVPMT'){
                //         $amount = $sline['refund_amount'];
                //         $statusInactive = 1;
                //     } else if($sline['voucher_type'] == 'FUNDTRANSFER'){
                //         $amount = $sline['fund_trancfer_amount'];
                //         $statusInactive = null;
                //     }

                //     $CommonFeeCollectionCount = CommonFeeCollection::where($cond)->whereDate('transaction_dt', $transaction_dt)->count();
                //     if($CommonFeeCollectionCount == 0){
                //         $insCommFeeColl = new CommonFeeCollection;
                //         $insCommFeeColl->branchID = !empty($branchData->id) ? $branchData->id : null;
                //         $insCommFeeColl->academic_year = $sline['academic_year'];
                //         $insCommFeeColl->voucher_type = $sline['voucher_type'];
                //         $insCommFeeColl->roll_number = $sline['roll_no'];
                //         $insCommFeeColl->amount = $amount;
                //         $insCommFeeColl->transaction_dt = $transaction_dt;
                //         $insCommFeeColl->inactive = $statusInactive;
                //         $insCommFeeColl->save();
                //         $CommonFeeCollectionData = CommonFeeCollection::select('id')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                //         $CommonFeeCollectionID = $CommonFeeCollectionData->id;
                //     } else {
                //         $CommonFeeCollectionData = CommonFeeCollection::select('id','amount')->where($cond)->whereDate('transaction_dt', $transaction_dt)->first();
                //         $CommonFeeCollectionID = $CommonFeeCollectionData->id;
                //         $insFeeColl = CommonFeeCollection::find($CommonFeeCollectionID);
                //         $insFeeColl->amount = $amount + $CommonFeeCollectionData->amount;
                //         $insFeeColl->save();
                //     }

                //     $FeeTypeID = null;
                //     if(!empty($branchData->id)){
                //         $feeTypeCount = FeeType::where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->count();
                //         if($feeTypeCount > 0){
                //             $feeTypeData = FeeType::select('id')->where(['fee_head' => $sline['fee_head'] , 'branchID' => $branchData->id])->first();
                //             $FeeTypeID = $feeTypeData->id;
                //         }
                //     }

                //     $insFinanTDetail = new CommonFeeCollectionHeadwise;
                //     $insFinanTDetail->branchID = !empty($branchData->id) ? $branchData->id : null;
                //     $insFinanTDetail->headID  = $FeeTypeID;
                //     $insFinanTDetail->commonFeeCollectionID  = $CommonFeeCollectionID;
                //     $insFinanTDetail->academic_year  = $sline['academic_year'];
                //     $insFinanTDetail->voucher_type  = $sline['voucher_type'];
                //     $insFinanTDetail->amount  = $amount;
                //     $insFinanTDetail->receipt_number  = $sline['receipt_no'];
                //     $insFinanTDetail->transaction_dt  = $transaction_dt;
                //     $insFinanTDetail->save();
                // }
            }
        });
    }

    public function getUniqueData()
    {
        return $this->uniqueData;
    }
    public function chunkSize(): int
    {
        return 10000;
    }
}
