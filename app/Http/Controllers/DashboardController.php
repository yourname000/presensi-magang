<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Models\Pengaturan;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // PARAMETER
        $prefix = config('session.prefix');
        $peran = session($prefix.'_peran');
        $id_user = session($prefix.'_id_user');

        // SET TITLE
        $data['title'] = 'Dashboard Admin';
        $data['icon'] = '<i class="fa-solid fa-gauge fs-3x text-white me-4"></i>';
        $data['subtitle'] = 'Selamat datang di sistem absensi karyawan!';

        $total_perizinan = 0;
        $total_karyawan = 0;

        $usr = User::get();
        if ($usr) {
            foreach ($usr as $key) {
                $total_karyawan += 1;
            }
        }


        $data['total_perizinan'] = $total_perizinan;
        $data['total_karyawan'] = $total_karyawan;

        return view('dashboard.index',$data);
    }

    public function profile()
    {
        // PARAMETER
        $prefix = config('session.prefix');
        $id_user = session($prefix.'_id_user');

        // SET TITLE
        $data['title'] = 'Profile';
        $data['subtitle'] = 'Personal biodata management';

        // GET DATA
        $result = User::where('id_user', $id_user)->where('deleted','N')->first();

        // SET DATA
        $data['result'] = $result;
        return view('dashboard.profile',$data);
    }


    // FUNCTION POST
   

    public function updateProfile(Request $request)
    {
        $arrVar = [
            'nama' => 'Nama lengkap'
        ];

        $post = [];
        $arrAccess = [];
        $data = ['required' => []];

        foreach ($arrVar as $var => $value) {
            $$var = $request->input($var);
            if (!$$var) {
                $data['required'][] = ['req_' . $var, "$value cannot be empty!"];
                $arrAccess[] = false;
            } else {
                $post[$var] = trim($$var);
                $arrAccess[] = true;
            }
        }

        // Jika ada input yang kosong, return error
        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        $prefix = config('session.prefix');
        $id_user = session($prefix.'_id_user');
        $name_image = $request->name_image;
        $result = User::where('id_user', $id_user)->first();

        if (!in_array(false, $arrAccess)) {
            $tujuan = public_path('data/user/');
            if (!File::exists($tujuan)) {
                File::makeDirectory($tujuan, 0755, true, true);
            }
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($tujuan, $fileName);
                
                if ($result->image && file_exists($tujuan . $result->image)) {
                    unlink($tujuan . $result->image);
                }
                
                $post['image'] = $fileName;
                session([
                    "{$prefix}_image"  => $fileName
                ]);
            } elseif (!$name_image) {
                if ($result->image && file_exists($tujuan . $result->image)) {
                    unlink($tujuan . $result->image);
                }
                $post['image'] = null;
            }

            $update = $result->update($post);
            if ($update) {
                session([
                    "{$prefix}_name"  => $post['name']
                ]);
                return response()->json(['status' => true, 'alert' => ['message' => 'profile changed successfully'], 'reload' => true]);
            } else {
                return response()->json(['status' => false, 'alert' => ['message' => 'profile failed to change']]);
            }
        }

        return response()->json(['status' => false]);
    }

    public function updateEmail(Request $request)
    {
        // Ambil data dari input
        $email = strtolower($request->email);
        $password = $request->password;
        $prefix = config('session.prefix');
        $id_user = Session::get("{$prefix}_id_user");

        // Validasi input
        if (!$email || !$password) {
            return response()->json(['status' => 700, 'message' => 'No data detected! Please enter data']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 700, 'message' => 'Invalid email! Please enter a valid email']);
        }

        // Cek user berdasarkan email
        $mail = User::where('email', $email)->where('id_user','!=',$id_user)->where('deleted','N')->first();
        $user = User::where('id_user',$id_user)->first();
        if ($email == $user->email) {
            return response()->json(['status' => 700, 'message' => 'No email changes detected']);
        }

        if (!$mail) {

            // Cek password
            if (Hash::check($password, $user->password)) {
                session([
                    "{$prefix}_email"  => $email
                ]);
                $user->email = $email;
                $user->save();
                return response()->json([
                    'status' => 200,
                    'message' => 'You have successfully changed your email!'
                ]);
            } else {
                return response()->json(['status' => 500, 'message' => 'Incorrect password! Please enter the correct password.']);
            }
        } else {
            return response()->json(['status' => 500, 'message' => 'Email already registered in the system!']);
        }
    }

    public function updatePassword(Request $request)
    {
        // Ambil data dari input
        $currentpassword = $request->currentpassword;
        $newpassword = $request->newpassword;
        $confirmpassword = $request->confirmpassword;
        $prefix = config('session.prefix');
        $id_user = Session::get("{$prefix}_id_user");

        // Validasi input
        if (!$confirmpassword || !$newpassword || !$currentpassword) {
            return response()->json(['status' => 700, 'message' => 'No data detected! Please enter data']);
        }


        $user = User::where('id_user',$id_user)->first();
        // Cek password
        if (Hash::check($currentpassword, $user->password)) {
            if ($newpassword === $confirmpassword) {
                $user->password = $newpassword;
                $user->save();
                return response()->json([
                    'status' => 200,
                    'message' => 'You have successfully changed your password!'
                ]);
            }else{
                return response()->json([
                    'status' => 500,
                    'message' => 'new password confirmation does not match'
                ]);
            }
            
        } else {
            return response()->json(['status' => 500, 'message' => 'Incorrect password! Please enter the correct password.']);
        }
    }

    public function accountDeactivated(Request $request)
    {
        $prefix = config('session.prefix');
        $id_user = Session::get("{$prefix}_id_user");

        $user = User::where('id_user',$id_user)->first();
        $user->status = 'N';
        $user->reason = 'you have deactivated your account';
        $user->blocked_date = now();
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Your account has been deactivated.',
            'redirect' => route('logout')
        ]);
    }



    public function pp_invoice(Request $request, $code = null)
    {
        $data = [];
        if (!$code) {
            return redirect()->route('dashboard');
        }
        $result = TransactionDetail::with(['transaction','transaction.user','transaction.category','creator'])->where('code',$code)->where('type',2)->first();
        if (!$result) {
            return redirect()->route('dashboard');
        }
        $setting = Setting::find(1);
        $cicil = TransactionDetail::where('id_transaction',$result->id_transaction)->where('type',2)->where('cicil','!=',null)->where('created_at','<',$result->created_at)->get();
        $setting = Setting::find(1);
        $total_cicil = 0;
        if ($cicil->isNotEmpty()) {
            foreach($cicil AS $row){
                $total_cicil += $row->cicil;
            }
        }

         $prefix = config('session.prefix');
        $id_user = session($prefix.'_id_user');


        $data['setting'] = $setting;
        $data['result'] = $result;
        $data['total_cicil'] = $total_cicil;
        $pdf = Pdf::loadView('invoice.pp',$data);

        // Tampilkan langsung di browser (tidak download)
        return $pdf->stream('invoice.pdf');
    }


}
