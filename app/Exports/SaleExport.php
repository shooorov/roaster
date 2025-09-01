<?php

namespace App\Exports;

use App\Data;
use App\Helpers;
use App\UseBranch;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SaleExport implements FromView
{
    public function __construct(private $start_date, private $end_date)
    {
    }

    public function view(): View
    {
        $end_date = Helpers::dayEndedAt($this->end_date);
        $start_date = Helpers::dayStartedAt($this->start_date);

        $items = Data::whereBranch(UseBranch::id())->summary($start_date, $end_date);

        return view('exports.sales', [
            'items' => $items,
        ]);
    }
}
