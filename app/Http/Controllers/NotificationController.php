<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\WelcomeStudent;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendTelegramMessage;

class NotificationController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService
    ) {}
    //
    public function sendMail(Request $request){

        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');

        // Mail::to($email)->queue(new WelcomeStudent($name));

        // Mail::to($email)->send(new WelcomeStudent($name));
        // $this->telegramService->send('<b>'.$message.'</b>');
        // SendTelegramMessage::dispatch('A new order was created.')
        //     ->onQueue('telegram')
        //     ->afterCommit();

        SendTelegramMessage::dispatch('A new order was created.')
            ->onQueue('telegram');

        return response()->json([
            "message" => "Mail Sent!"
        ]);
    }
}
