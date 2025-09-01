<?php

namespace App\Console\Commands;

use App\Data;
use App\Helpers;
use App\Mail\SaleReport;
use App\Models\Branch;
use App\Models\EmailDigest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSaleReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:report {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mail about sale report to users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $branches = Branch::all();
        $debug = $this->option('debug');

        foreach ($branches as $branch) {
            $user_ids = EmailDigest::where('is_checked', true)->where('branch_id', $branch->id)->pluck('user_id');
            $user_emails = User::whereIn('id', $user_ids)->get()->pluck('email')->toArray();

            $recipients = array_unique($user_emails);

            $start_date = Helpers::dayStartedAt();
            $end_date = Helpers::dayEndedAt();

            $params = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'date' => $end_date,
                'cards' => Data::whereBranch($branch->id)->summary($start_date, $end_date),
            ];

            if ($debug) {
                Mail::to('admin@example.com')->queue(new SaleReport($params));
                Log::info($branch->name.' branch sales report sent --debug');

                continue;
            }
            if (count($recipients) > 0) {
                foreach ($recipients as $recipient) {
                    Log::info($recipient.' getting mail from '.$branch->name.' branch');
                }

                Mail::to($recipients)->bcc([])->queue(new SaleReport($params));

                Log::info($branch->name.' branch sales report sent');
            }

        }

        return 0;
    }
}
