<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\ShortSchedule\ShortSchedule;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CustomTokenDeposit::class,
        Commands\TokenDepositCommand::class,
        Commands\AdjustCustomTokenDeposit::class,
        Commands\BuyOrderCommand::class,
        Commands\SellOrderCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        storeException('Schedule',date('Y-m-d H:i:s'));
//        $schedule->command('custom-token-deposit')->everyTenMinutes();
//        $schedule->command('command:erc20token-deposit')->everyTenMinutes();
//        $schedule->command('buy:order')->everyMinute();
//        $schedule->command('sell:order')->everyMinute();
    }

    protected function shortSchedule(ShortSchedule $shortSchedule)
    {
        if(allsetting('enable_bot_trade') == STATUS_ACTIVE) {
            $buyInterval = settings('trading_bot_buy_interval') ?? 10;
            $buyInterval = intval($buyInterval);
            $sellInterval = settings('trading_bot_sell_interval') ?? 10;
            $sellInterval = intval($sellInterval);
            $shortSchedule->command('buy:order')->everySeconds($buyInterval)->withoutOverlapping();
            $shortSchedule->command('sell:order')->everySeconds($sellInterval)->withoutOverlapping();
        } else {
            storeException('ShortSchedule deactive',date('Y-m-d H:i:s'));
        }
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
