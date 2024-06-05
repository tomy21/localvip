<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\RefGate;
use Illuminate\Support\Str as Str2;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\api\Str;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GateController extends Controller
{
    // Get all gates
    public function index()
    {
        $gates = RefGate::all();
        return response()->json($gates);
    }

    public function storeByPM(Request $request)
    {
        // Simpan file di storage/public/fotos
        // $foto1 = $request->file('foto1')->store('public/fotos');
        // $foto2 = $request->file('foto2')->store('public/fotos');

        // Path lengkap ke file
        // $foto1Path = asset('storage/fotos/' . basename($foto1));
        // $foto2Path = asset('storage/fotos/' . basename($foto2));


        // Kirim request ke API Node.js
        // $client = new Client();
        // $url = 'http://localhost:3009/api/addData';

        // $response = $client->post($url, [
        //     'multipart' => [
        //         [
        //             'name'     => 'LocationName',
        //             'contents' => 'SKY ARYADUTA',
        //         ],
        //         [
        //             'name'     => 'InTime',
        //             'contents' => now()->format('Y-m-d H:i:s'),
        //         ],
        //         [
        //             'name'     => 'foto1',
        //             'contents' => fopen($request->file('foto1')->getPathname(), 'r'),
        //             'filename' => $request->file('foto1')->getClientOriginalName(),
        //         ],
        //         [
        //             'name'     => 'foto2',
        //             'contents' => fopen($request->file('foto2')->getPathname(), 'r'),
        //             'filename' => $request->file('foto2')->getClientOriginalName(),
        //         ],
        //     ],
        // ]);

        // Ambil VihiclePlate dari respons API Node.js
        // dd($response);
        // die;
        // $vihiclePlate = $response['data']['VihiclePlate'];

        // Simpan data ke tabel RefGate
        $image = $this->captureSnapshot('http://192.168.0.12/cgi-bin/snapshot.cgi?channel=1', 'back');
        $image2 = $this->captureSnapshot('http://192.168.0.13/cgi-bin/snapshot.cgi?channel=1', 'front');
        // dd($image);
        $imageArray = [
            'image' => $image,
            'image2' => $image2,
        ];
        // dd($imageArray);
        $gate = new RefGate;
        $gate->CodeGate = "PM";
        $gate->LocationCode = "009SK";
        $gate->VihiclePlate = "B123XX";
        $gate->InTime = now();
        $gate->status = "IN";
        $gate->save();

        // dd(response()->json());
        return response()->json([
            'message' => 'Data stored successfully and sent to Node.js API',
            'data' => $gate,
            // 'nodeResponse' => json_decode($response->getBody()->getContents(), true)
        ], 201);
    }
    public function storeByPK()
    {
        // Ambil snapshot dari kamera
        $image = $this->captureSnapshot('http://192.168.0.10/cgi-bin/snapshot.cgi?channel=1', 'front');
        $image2 = $this->captureSnapshot('http://192.168.0.11/cgi-bin/snapshot.cgi?channel=1', 'back');

        // Simpan snapshot sementara untuk pengiriman
        $imagePath = storage_path('app/public/front_temp_image.jpg');
        file_put_contents($imagePath, $image);

        $image2Path = storage_path('app/public/back_temp_image2.jpg');
        file_put_contents($image2Path, $image2);

        // Kirim request ke API Node.js
        $client = new Client();
        $url = 'https://dev-apicount.skyparking.online/occ/UploadFoto';

        $response = $client->post($url, [
            'multipart' => [
                [
                    'name'     => 'locationName',
                    'contents' => 'Maxxbox',
                ],
                [
                    'name' => 'gateCode',
                    'contents' => 'Out'
                ],
                [
                    'name'     => 'time',
                    'contents' => now()->format('Y-m-d H:i:s'),
                ],
                [
                    'name'     => 'files',
                    'contents' => fopen($imagePath, 'r'),
                    'filename' => 'front_temp_image.jpg',
                ],
                [
                    'name'     => 'files',
                    'contents' => fopen($image2Path, 'r'),
                    'filename' => 'back_temp_image2.jpg',
                ],
            ],
        ]);

        $responseBody = json_decode($response->getBody(), true);

        // Ambil VihiclePlate dari respons API Node.js
        // dd($responseBody);
        // die;
        $responsePlate = $responseBody[0]['front'] ?? $responseBody[0]['back'] ?? 'UNKNOWN'; // Pastikan ini sesuai dengan format respons Anda

        // Simpan data ke tabel RefGate
        $gate = new RefGate;
        $gate->CodeGate = "PK";
        $gate->LocationCode = "009SK";
        $gate->VihiclePlate = $responsePlate;
        $gate->InTime = now();
        $gate->status = "IN";
        $gate->save();

        // Hapus file sementara setelah selesai
        unlink($imagePath);
        unlink($image2Path);

        return response()->json([
            'message' => 'Data stored successfully and sent to Node.js API',
            'data' => $gate,
        ], 201);
    }

    public function showById($id)
    {
        $gate = RefGate::find($id);

        if ($gate) {
            return response()->json($gate);
        }

        return response()->json(['message' => 'Gate not found'], 404);
    }

    public function captureSnapshot($urlChannel, $desc)
    {
        // URL yang akan diakses
        $url = $urlChannel . '/cgi-bin/snapshot.cgi?channel=1';

        // Informasi akun
        $username = "admin";
        $password = "admin123";

        // Mengirim permintaan pertama tanpa autentikasi untuk mendapatkan informasi autentikasi
        $response = Http::withoutVerifying()->get($url);

        // Mendapatkan header WWW-Authenticate dari respons pertama
        $wwwAuthenticateHeader = $response->header('WWW-Authenticate');

        // Mendapatkan informasi autentikasi dari header
        $authInfo = [];
        preg_match_all('/(\w+)="([^"]+)"/', $wwwAuthenticateHeader, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $authInfo[$match[1]] = $match[2];
        }

        // Membangun nilai autentikasi Digest
        $nonce = $authInfo['nonce'];
        $realm = $authInfo['realm'];
        $qop = $authInfo['qop'];
        $cnonce = substr(md5(uniqid(mt_rand(), true)), 0, 16); // Nilai acak untuk cnonce
        $nc = '00000002'; // Counter
        $uri = 'cgi-bin/snapshot.cgi?channel=1'; // Sesuaikan dengan URI yang sesuai dengan kebutuhan Anda

        // Menghitung nilai respons Digest menggunakan MD5
        $ha1 = md5("$username:$realm:$password");
        $ha2 = md5("GET:$uri");
        $responseDigest = md5("$ha1:$nonce:$nc:$cnonce:$qop:$ha2");

        // Mengirim permintaan kedua dengan autentikasi Digest
        $response = Http::withHeaders([
            'Authorization' => "Digest username=\"$username\", realm=\"$realm\", nonce=\"$nonce\", uri=\"$uri\", qop=\"$qop\", nc=$nc, cnonce=\"$cnonce\", response=\"$responseDigest\"",
            'Accept-Encoding' => 'gzip, deflate',
        ])->get($url);

        $photo = $response->getBody()->getContents();
        $filename = $desc . date('YmdHis') . '_' . Str2::uuid() . '.jpg';
        $path = 'public/issues/' . $filename;
        Storage::put($path, $photo);
        // $imagePath = storage_path('app/public/issues/snapshots/') . uniqid() . '.png';
        // $findIssues->foto = $filename;
        // $findIssues->save();

        $data = [
            'image_path' => asset('storage/issues/' . $filename),
            'filename' => $filename,
        ];

        return $data;
    }
}
