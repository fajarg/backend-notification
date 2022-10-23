<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class NotificationController extends Controller
{
    public function send_notification()
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken =
            'c_lxKFgRms_WWRs5fKjA0d:APA91bFh3mQszPI9n620C2gt81oTKjem5xHT_49PycBYE3sU4s1n4iGuTEF7koaiGIq8yStQnoBqX0_vCWvbu-sIQWnjAq9QaE70DrDgxXwMJLOpnFfTK347rK30Sazfzha3dISLc5nk';

        $serverKey =
            'AAAA9m2BaQE:APA91bFmKONIWjxlLQ7AQCc--f0nkbGK-1hLKbN22YgtyQ3J6SBm8FT8PfYcoX_iJ4lP5RJuOdIFMEuPPICrOBxbOflNyHCAdXCLBlXrewRV2RS0PlhIGjvC_SVCQ6AqxKGgr1Z-SNqI';

        $data = [
            'to' => $FcmToken,
            // 'notification' => [
            //     'title' => 'This is title for notification',
            //     'body' => 'This is the body fill for the notification',
            // ],
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);

        DB::table('notification')->insert([
            'title' => 'This is title for notification',
            'body' => 'This is the body fill for the notification',
            'isRead' => 'false',
            'created_date' => now(),
        ]);

        // FCM response
        dd($result);
    }

    public function get_notification()
    {
        $data_notif = DB::table('notification')->get();
        $data_isRead = DB::table('notification')
            ->where('isRead', 'false')
            ->latest('created_date')
            ->get();

        $isRead = count($data_isRead);

        if ($isRead > 0) {
            $badge = false;
        } else {
            $badge = true;
        }

        return response()->json([
            'data_notif' => $data_notif,
            'newMessage' => $isRead,
            'badge' => $badge,
        ]);
    }

    public function set_notification_is_read()
    {
        DB::table('notification')
            ->where('isRead', 'false')
            ->update(['isRead' => 'true']);

        return response()->json([
            'message' => 'notif mark as read',
        ]);
    }
}
