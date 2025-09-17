<?php

if (!function_exists('jiny_auth')) {
    /**
     * Jiny Auth helper function
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function jiny_auth($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('jiny-auth');
        }

        return config("jiny-auth.{$key}", $default);
    }
}

if (!function_exists('is_email_verified')) {
    /**
     * Check if user email is verified
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return bool
     */
    function is_email_verified($user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }

        return !is_null($user->email_verified_at);
    }
}

if (!function_exists('has_2fa_enabled')) {
    /**
     * Check if user has 2FA enabled
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return bool
     */
    function has_2fa_enabled($user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }

        return !empty($user->two_factor_secret);
    }
}

if (!function_exists('is_password_expired')) {
    /**
     * Check if user password is expired
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return bool
     */
    function is_password_expired($user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user || !$user->password_changed_at) {
            return false;
        }

        $expirationDays = config('jiny-auth.password.expiration_days', 90);
        
        if ($expirationDays <= 0) {
            return false;
        }

        return now()->diffInDays($user->password_changed_at) > $expirationDays;
    }
}

if (!function_exists('generate_username')) {
    /**
     * Generate unique username from email
     *
     * @param string $email
     * @return string
     */
    function generate_username($email)
    {
        $username = strstr($email, '@', true);
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
        
        $model = config('jiny-auth.user.model', 'App\\Models\\User');
        $originalUsername = $username;
        $counter = 1;
        
        while ($model::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
}

if (!function_exists('get_user_roles')) {
    /**
     * Get user roles
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return \Illuminate\Support\Collection
     */
    function get_user_roles($user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return collect();
        }

        if (method_exists($user, 'getRoles')) {
            return $user->getRoles();
        }

        if (method_exists($user, 'roles')) {
            return $user->roles()->pluck('name');
        }

        return collect();
    }
}

if (!function_exists('user_has_role')) {
    /**
     * Check if user has specific role
     *
     * @param string $role
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return bool
     */
    function user_has_role($role, $user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        return get_user_roles($user)->contains($role);
    }
}

if (!function_exists('user_has_permission')) {
    /**
     * Check if user has specific permission
     *
     * @param string $permission
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return bool
     */
    function user_has_permission($permission, $user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        return false;
    }
}

if (!function_exists('get_login_redirect')) {
    /**
     * Get login redirect path
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return string
     */
    function get_login_redirect($user = null)
    {
        $user = $user ?: auth()->user();
        
        $default = config('jiny-auth.redirects.login', '/dashboard');
        
        if (!$user) {
            return $default;
        }

        // Check for role-based redirects
        if (user_has_role('admin', $user)) {
            return '/admin/dashboard';
        }

        return $default;
    }
}

if (!function_exists('get_logout_redirect')) {
    /**
     * Get logout redirect path
     *
     * @return string
     */
    function get_logout_redirect()
    {
        return config('jiny-auth.redirects.logout', '/');
    }
}

if (!function_exists('format_login_attempt_message')) {
    /**
     * Format login attempt message
     *
     * @param int $attempts
     * @param int $maxAttempts
     * @return string
     */
    function format_login_attempt_message($attempts, $maxAttempts)
    {
        $remaining = $maxAttempts - $attempts;
        
        if ($remaining <= 0) {
            return __('Your account has been locked due to too many failed login attempts.');
        }
        
        if ($remaining == 1) {
            return __('Invalid credentials. You have 1 attempt remaining before your account is locked.');
        }
        
        return __('Invalid credentials. You have :remaining attempts remaining.', ['remaining' => $remaining]);
    }
}

if (!function_exists('validate_password_strength')) {
    /**
     * Validate password strength
     *
     * @param string $password
     * @return array
     */
    function validate_password_strength($password)
    {
        $errors = [];
        $config = config('jiny-auth.password');
        
        if (strlen($password) < $config['min_length']) {
            $errors[] = __('Password must be at least :min characters.', ['min' => $config['min_length']]);
        }
        
        if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = __('Password must contain at least one uppercase letter.');
        }
        
        if ($config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = __('Password must contain at least one lowercase letter.');
        }
        
        if ($config['require_numeric'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = __('Password must contain at least one number.');
        }
        
        if ($config['require_special_char'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = __('Password must contain at least one special character.');
        }
        
        return $errors;
    }
}

if (!function_exists('mask_email')) {
    /**
     * Mask email address for privacy
     *
     * @param string $email
     * @return string
     */
    function mask_email($email)
    {
        $parts = explode('@', $email);
        
        if (count($parts) != 2) {
            return $email;
        }
        
        $name = $parts[0];
        $domain = $parts[1];
        
        if (strlen($name) <= 3) {
            $maskedName = str_repeat('*', strlen($name));
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 3) . substr($name, -1);
        }
        
        return $maskedName . '@' . $domain;
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * Get client IP address
     *
     * @return string|null
     */
    function get_client_ip()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
        
        return request()->ip();
    }
}

if (!function_exists('get_browser_info')) {
    /**
     * Get browser information from user agent
     *
     * @param string|null $userAgent
     * @return array
     */
    function get_browser_info($userAgent = null)
    {
        $userAgent = $userAgent ?: request()->userAgent();
        
        $browser = 'Unknown';
        $platform = 'Unknown';
        
        // Detect browser
        if (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        }
        
        // Detect platform
        if (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $platform = 'iOS';
        }
        
        return [
            'browser' => $browser,
            'platform' => $platform,
            'user_agent' => $userAgent,
        ];
    }
}