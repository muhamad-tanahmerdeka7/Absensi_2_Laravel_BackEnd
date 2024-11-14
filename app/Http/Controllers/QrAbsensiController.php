<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\QrAbsen;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Writer\PngWriter;

class QrAbsensiController extends Controller
{
    // generateQrCodeCheckin
    public function generateQrCodeCheckin()
    {

        do {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = substr(str_shuffle($characters), 0, 6);
        } while (QrAbsen::where('qr_checkin', $code)->exists());

        return $code;
    }

    // generateQrCodeCheckout
    public function generateQrCodeCheckout()
    {
        do {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = substr(str_shuffle($characters), 0, 6);
        } while (QrAbsen::where('qr_checkout', $code)->exists());

        return $code;
    }



    // generateQrCode
    private function generateQrCode($data)
    {
        $qrCode = QrCode::create($data)
            ->setSize(300)
            ->setMargin(10);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return base64_encode($result->getString());
    }

    // index
    public function index(Request $request)

    {
        $qrAbsen = QrAbsen::paginate(10);
        // $qrAbsen = QrAbsen::when($request->input('date'), function ($query, $name) {
        //     $query->whereHas('user', function ($query) use ($name) {
        //         $query->where('date', 'like', '%' . $name . '%');
        //     });
        // })->orderBy('id', 'desc')->paginate(10);
        return view('pages.qr_absen.index', compact('qrAbsen'));
    }
    // show
    public function show($id)
    {
        $qrAbsen = QrAbsen::find($id);
        return view('pages.qr_absen.show', compact('qrAbsen'));
    }
    // create
    public function create()
    {
        return view('pages.qr_absen.create');
    }

    // store

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = Carbon::createFromFormat('Y-m', $request->month);
        $daysInMonth = $month->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->copy()->setDay($day);

            QrAbsen::create([
                'date' => $date->format('Y-m-d'),
                'qr_checkin' => $this->generateQRCodeCheckin(),
                'qr_checkout' => $this->generateQRCodeCheckout(),
            ]);
        }

        return redirect()->route('qr_absens.index')->with('success', 'QR codes generated successfully for ' . $month->format('F Y'));
    }

    public function downloadPDF($id)
    {
        $qrAbsen = QrAbsen::findOrFail($id);

        // Generate QR codes as base64 images
        $qrCodeCheckin = $this->generateQrCode($qrAbsen->qr_checkin);
        $qrCodeCheckout = $this->generateQrCode($qrAbsen->qr_checkout);

        $data = [
            'qrAbsen' => $qrAbsen,
            'qrCodeCheckin' => $qrCodeCheckin,
            'qrCodeCheckout' => $qrCodeCheckout,
        ];

        $pdf = Pdf::loadView('pages.pdf.qr_absen', $data);

        return $pdf->download('qr_absen_' . $qrAbsen->date . '.pdf');
    }
}
