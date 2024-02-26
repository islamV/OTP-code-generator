<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(){
      $phone='';
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://graph.facebook.com/v18.0/180573095147077/messages',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
          "messaging_product": "whatsapp",
          "recipient_type": "individual",
          "to": "'.$phone.'",
          "type": "template",
          "template": {
            "name": "welcome",
            "language": {
              "code": "ar"
            },
          
          }
        }',
        CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer EAAJq625SX70BO3MZALUl1USGGqH8yGYgtYeU1SIdvs2v4mOh2LepZCmZAykUZBgz4QOu3RupuBfWMdXZBnbsJ2GODjtMLZA3zydnOo2zu3dvD70rDNJ2akLpE3qsSyegu4CLwL15fTbzVn7G1CIZBV94BZBjLFVYk0WvmliClGchmkiRpRLOVOfVTRZCZBAtfAZBW5bFRrdxZASJzGyS5izrzBulAlLFJKmPIYybDrwZD',
          'Content-Type: application/json'
        ),
      ));
      $response = curl_exec($curl);

      curl_close($curl);
        return $response ;
        
    }
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
