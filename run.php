<?php

/* 
* HARAM UNTUK DIJUAL LAGI
* Created By: Jumady (https://web.facebook.com/dyvretz/)
*/

error_reporting(0);
date_default_timezone_set("Asia/Jakarta");
require 'function.php';

$colors = new Colors();
echo '------------------- '.$colors->getColoredString("AUTO REFF LION PARCEL WITH SMSHUB", "green").' -------------------'.PHP_EOL.PHP_EOL;

if(!file_exists("alerts.txt")) {
    inputApikeyOTP:
    $updated = input('[ '.date('H:i:s').' ] -> Apakah Sudah edit config.json? (y/N)');
    if(strtolower($updated) == 'y') {
        file_put_contents("alerts.txt", "off");
    } else if(strtolower($updated) == 'n') {
        inputKey:
        $key = input('[ '.date('H:i:s').' ] -> Apikey Kamu');
        $durasi = input('[ '.date('H:i:s').' ] -> Waktu Tunggu OTP (detik)');
        file_put_contents("config.json", json_encode(['apikey' => $key, 'durasiOTP' => $durasi]));
    } else {
        echo '[ '.date('H:i:s').' ] -> '.$colors->getColoredString("Pilihan Tidak Tersedia", "red").PHP_EOL;
        goto inputApikeyOTP;
    }
}
$readConfig  = json_decode(file_get_contents("config.json"), true);
$apikey = trim($readConfig['apikey']);
$durasiOTP = trim($readConfig['durasiOTP']);

if ($apikey) {
    echo '[ '.date('H:i:s').' ] -> Apikey Ditemukan: '.$colors->getColoredString($apikey, "green").PHP_EOL;
} else {
    echo '[ '.date('H:i:s').' ] -> Apikey Tidak Ditemukan'.PHP_EOL;
    echo '[ '.date('H:i:s').' ] -> Silakan Input Data Apikey'.PHP_EOL;
    goto inputKey;
}

/** RANDOM NAMA  **/
$name = get_between(nama(), '{"name":"', '",');
$exNama = explode(' ', $name);
$nama = $exNama[0];
/** END RANDOM NAMA  **/
    
/** CHECK SALDO SMSHUB **/
$url = 'smshub.org';
$saldo = explode(':', GetBalance($url, $apikey));
$saldo = $saldo[1];
echo '[ '.date('H:i:s').' ] -> Sisa Saldo SMSHUB: '.$saldo.' RUB'.PHP_EOL;
/** END CHECK SALDO SMSHUB **/
inpuReff:
$codeReff = input('[ '.date('H:i:s').' ] -> Kode Refferal');
$codeReff = strtoupper($codeReff);
$totalReff = input('[ '.date('H:i:s').' ] -> Jumlah Refferal');
for ($ia=1; $ia <= $totalReff; $ia++) {
    echo '---------------------------- '.$colors->getColoredString("REFFERAL KE $ia", "green").' -----------------------------'.PHP_EOL;
    $checkReff = curlget('https://algo-api.lionparcel.com/v1/account/referral_code/check?code='.$codeReff, null, null);
    $statusOTP = get_between($checkReff[1], '{"success":', '}');
    if ($statusOTP) {
        echo '[ '.date('H:i:s').' ] -> Refferal '.$codeReff.' Benar.'.PHP_EOL;
        ulangMintaNomor:
        $getNumber = getNumber($url, $apikey);
        if(preg_match("/ACCESS_NUMBER/", $getNumber)) {
            $exGet = explode(':', $getNumber);
            $idOrder = $exGet[1];
            $nomorHP = '+'.$exGet[2];
            echo '[ '.date('H:i:s').' ] -> Mencoba Mendaftar Dengan Nomor '.$colors->getColoredString($nomorHP, "green").PHP_EOL;
            $checkNomor = curlget('https://algo-api.lionparcel.com/v1/account/auth/customer/username/check?phone_number='.$nomorHP, null, null);
            $statusNomor = get_between($checkNomor[1], '{"success":', '}');
            if ($nomorHP) {
                echo '[ '.date('H:i:s').' ] -> Nomor '.$colors->getColoredString($nomorHP, "green").' Tersedia.'.PHP_EOL;
                $data = '{"messaging_type":"SMS","otp_type":"REGISTER","phone_number":"'.$nomorHP.'","role":"CUSTOMER"}';
                $lenght = strlen($data);
                $headers = [
                    'Host: algo-api.lionparcel.com', 
                    'cache-control: max-age=0',  
                    'content-type: application/json; charset=UTF-8',  
                    'content-length: '.$lenght,  
                    'accept-encoding: gzip',  
                    'user-agent: okhttp/5.0.0-alpha.6', 
                ];

                $getOTP = curl('https://algo-api.lionparcel.com/v2/account/auth/otp/request', $data, $headers);
                $statusOTP = get_between($getOTP[1], '"otp_id":', ',"');
                $expOTP = get_between($getOTP[1], ',"expired_in":', ',"');
                if ($statusOTP) {
                    echo '[ '.date('H:i:s').' ] -> Berhasil Mengirim OTP. Expired Dalam '.$expOTP.' detik'.PHP_EOL;
                    $time = time();
                    echo '[ '.date('H:i:s').' ] -> Sedang Menunggu OTP Selama '.$durasiOTP.' detik'.PHP_EOL;
                    CheckUlangOTP:
                    $funcOTPRegist = GetOtp($url, $apikey, $idOrder);
                    $otp = explode(':', $funcOTPRegist);
                    $otp = $otp[1];
                    if ($otp) {
                        $otp = get_between($otp, 'Kode OTP untuk akun Lion Parcel mu adalah ', '.');
                        $otp = trim($otp);
                        echo '[ '.date('H:i:s').' ] -> OTP: '.$colors->getColoredString($otp, "green").PHP_EOL;
                    } else {
                        if (time()-$time > $durasiOTP) {
                            echo '[ '.date('H:i:s').' ] -> Gagal Mendapatkan OTP.'.PHP_EOL;
                            $funcDeleteOtp = ChangeCancel($url, $apikey, $idOrder);
                            goto ulangMintaNomor;
                        } else {
                            goto CheckUlangOTP;
                        }
                    }
                    $data = 'otp_id='.$statusOTP.'&otp='.$otp.'';
                    $lenght = strlen($data);
                    $headers = [
                        'Host: algo-api.lionparcel.com', 
                        'cache-control: max-age=0',
                        'content-type: application/x-www-form-urlencoded', 
                        'content-length: '.$lenght,
                        'user-agent: okhttp/5.0.0-alpha.6',
                    ];

                    $tryOTP = curl('https://algo-api.lionparcel.com/v1/account/auth/otp/exchange', $data, $headers);
                    $token = get_between($tryOTP[1], '{"token":"', '",');
                    if ($token) {
                        echo '[ '.date('H:i:s').' ] -> Berhasil Verifikasi OTP.'.PHP_EOL;
                        $data = '{"city":8976,"fullname":"'.$nama.'","password":"jumady21","password_confirm":"jumady21","phone_number":"'.$nomorHP.'","referral_code":"'.$codeReff.'","token":"'.$token.'"}';
                        $lenght = strlen($data);
                        $headers = [
                            'Host: algo-api.lionparcel.com', 
                            'cache-control: max-age=0', 
                            'content-type: application/json; charset=UTF-8', 
                            'content-length: '.$lenght,  
                            'user-agent: okhttp/5.0.0-alpha.6',
                        ];
                        $getRegist = curl('https://algo-api.lionparcel.com/v3/account/auth/customer/register', $data, $headers);
                        $statusRegist = get_between($getRegist[1], '{"success":', '}');
                        if ($statusRegist) {
                            echo '[ '.date('H:i:s').' ] -> Sukses Mendaftar Dengan Kode Reff '.$colors->getColoredString($codeReff, "green").PHP_EOL;
                            file_put_contents('accLionParcel.txt', $nomorHP.'|jumady21'.PHP_EOL, FILE_APPEND);
                        } else {
                            $messageRegist = get_between($getRegist[1], ',"id":"', '"}}');
                            $ChangeCancel = ChangeCancel($url, $apikey, $idOrder);
                            echo '[ '.date('H:i:s').' ] -> Gagal Mendaftar. Alasan: '.$colors->getColoredString($messageRegist, "red").PHP_EOL;
                        }
                    } else {
                        $ChangeCancel = ChangeCancel($url, $apikey, $idOrder);
                        echo '[ '.date('H:i:s').' ] -> Gagal Verifikasi OTP.'.PHP_EOL;
                    }
                } else {
                    $messageOTP = get_between($getOTP[1], ',"id":"', '"}}');
                    $ChangeCancel = ChangeCancel($url, $apikey, $idOrder);
                    echo '[ '.date('H:i:s').' ] -> Gagal Mengirim OTP. Alasan: '.$colors->getColoredString($messageOTP, "red").PHP_EOL;
                }
            } else {
                $ChangeCancel = ChangeCancel($url, $apikey, $idOrder);
                echo '[ '.date('H:i:s').' ] -> Nomor '.$colors->getColoredString($nomorHP, "green").' Tidak Tersedia.'.PHP_EOL;
            }
        } else {
            echo '[ '.date('H:i:s').' ] -> Gagal Mendapatkan Nomor. Check saldo/stok OTP'.PHP_EOL;
        }
    } else {
        $messageReff = get_between($checkReff[1], ',"id":"', '"}}');
        echo '[ '.date('H:i:s').' ] -> Refferal '.$codeReff.' Salah. Alasan: '.$colors->getColoredString($messageReff, "red").PHP_EOL;
        goto inpuReff;
    }
}