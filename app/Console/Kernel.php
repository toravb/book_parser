<?php

namespace App\Console;

use App\Http\Middleware\TrimStrings;
use App\Jobs\GenerateExcelJob;
use App\Jobs\ParseImageJob;
use App\Jobs\ParsePageJob;
use App\Jobs\ParseSitemapJob;
//use App\Models\Site;
use App\Parser\Controllers\SiteMap;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Parser\Controllers\ProxyAggregator;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {


//        $schedule->command('queue:restart')
//            ->everyThirtyMinutes();

//        $schedule->command('queue:work --name=doParsePages --queue=doParsePages  --daemon')
//            ->everyMinute()
//            ->withoutOverlapping();
//        $schedule->command('queue:work --name=doParseImages --queue=doParseImages  --daemon')
//            ->everyMinute()
//            ->withoutOverlapping();
//        $schedule->command('queue:work --queue=default --timeout=0  --daemon')
//            ->everyMinute()
//            ->withoutOverlapping();
//
        $basicdecor = DB::table('sites')->where('site', '=', 'basicdecor.ru')
            ->select()->first();

        $schedule->job((new ParsePageJob)::dispatchIf($basicdecor->doParsePages)->onQueue('doParsePages'))->everyFiveMinutes();
        $schedule->job((new ParseImageJob())::dispatchIf($basicdecor->doParseImages)->onQueue('doParseImages'))->everyFiveMinutes();
        $schedule->job((new GenerateExcelJob())::dispatchIf($basicdecor->downloadedExcel)->onQueue('default'))->everyMinute();
        $schedule->job((new ParseSitemapJob())::dispatchIf($basicdecor->doParseSitemap)->onQueue('default'))->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {

        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
