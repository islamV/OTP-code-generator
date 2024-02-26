<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Twilio\Rest\Client;
use App\Notifications\OTP;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
         $user  = User::where('email', $this->input('email'))->first();
         $user->genrateOTPCode();
         // mail
        
        $user->notify(new OTP);
                 // $phone='';
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //   CURLOPT_URL => 'https://graph.facebook.com/v18.0/180573095147077/messages',
        //   CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_ENCODING => '',
        //   CURLOPT_MAXREDIRS => 10,
        //   CURLOPT_TIMEOUT => 0,
        //   CURLOPT_FOLLOWLOCATION => true,
        //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //   CURLOPT_CUSTOMREQUEST => 'POST',
        //   CURLOPT_POSTFIELDS =>'{
        //     "messaging_product": "whatsapp",
        //     "recipient_type": "individual",
        //     "to": "'.$phone.'",
        //     "type": "template",
        //     "template": {
        //       "name": "welcome",
        //       "language": {
        //         "code": "ar"
        //       },
            
        //     }
        //   }',
        //   CURLOPT_HTTPHEADER => array(
        //     'Authorization: Bearer EAAJq625SX70BO3MZALUl1USGGqH8yGYgtYeU1SIdvs2v4mOh2LepZCmZAykUZBgz4QOu3RupuBfWMdXZBnbsJ2GODjtMLZA3zydnOo2zu3dvD70rDNJ2akLpE3qsSyegu4CLwL15fTbzVn7G1CIZBV94BZBjLFVYk0WvmliClGchmkiRpRLOVOfVTRZCZBAtfAZBW5bFRrdxZASJzGyS5izrzBulAlLFJKmPIYybDrwZD',
        //     'Content-Type: application/json'
        //   ),
        // ));
        // $response = curl_exec($curl);
  
        // curl_close($curl);
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
