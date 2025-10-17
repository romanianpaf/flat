<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    private const CACHE_KEY = 'weather_chitilei';
    private const CACHE_TTL = 600; // 10 minute în secunde
    
    // Coordonate pentru Șoseaua Chitilei 242D, București
    private const LOCATION = [
        'lat' => 44.5061,
        'lon' => 26.0506,
        'name' => 'București, Chitilei'
    ];

    /**
     * Obține vremea curentă (cu cache)
     * 
     * Optimizare: Un singur call la OpenWeatherMap la fiecare 10 minute,
     * indiferent câți utilizatori accesează aplicația.
     * 
     * @return JsonResponse
     */
    public function getCurrentWeather(): JsonResponse
    {
        // Încearcă să obții din cache
        $weather = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchWeatherFromApi();
        });

        if (!$weather) {
            return response()->json([
                'error' => 'Nu s-a putut încărca vremea',
                'data' => $this->getMockWeather()
            ], 503);
        }

        return response()->json([
            'data' => $weather
        ]);
    }

    /**
     * Fetch weather data from OpenWeatherMap API
     */
    private function fetchWeatherFromApi(): ?array
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        
        if (!$apiKey) {
            \Log::warning('OpenWeatherMap API Key nu este configurat în .env');
            return $this->getMockWeather();
        }

        try {
            $response = Http::timeout(5)->get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => self::LOCATION['lat'],
                'lon' => self::LOCATION['lon'],
                'appid' => $apiKey,
                'units' => 'metric',  // Celsius
                'lang' => 'ro'        // Română
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatWeatherData($data);
            }

            \Log::error('OpenWeatherMap API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $this->getMockWeather();

        } catch (\Exception $e) {
            \Log::error('Exception fetching weather', [
                'message' => $e->getMessage()
            ]);
            return $this->getMockWeather();
        }
    }

    /**
     * Formatează datele de la API
     */
    private function formatWeatherData(array $data): array
    {
        return [
            'location' => self::LOCATION['name'],
            'temperature' => round($data['main']['temp']),
            'feelsLike' => round($data['main']['feels_like']),
            'description' => $data['weather'][0]['description'] ?? 'necunoscut',
            'icon' => $this->getWeatherIcon($data['weather'][0]['icon'] ?? '01d'),
            'humidity' => $data['main']['humidity'],
            'pressure' => $data['main']['pressure'],
            'windSpeed' => $data['wind']['speed'] ?? 0,
            'cloudiness' => $data['clouds']['all'] ?? 0,
            'sunrise' => isset($data['sys']['sunrise']) ? date('Y-m-d H:i:s', $data['sys']['sunrise']) : null,
            'sunset' => isset($data['sys']['sunset']) ? date('Y-m-d H:i:s', $data['sys']['sunset']) : null,
            'cached_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Obține iconița potrivită pentru vremea curentă
     */
    private function getWeatherIcon(string $iconCode): string
    {
        $iconMap = [
            '01d' => '☀️',  // clear sky day
            '01n' => '🌙',  // clear sky night
            '02d' => '⛅',  // few clouds day
            '02n' => '☁️',  // few clouds night
            '03d' => '☁️',  // scattered clouds
            '03n' => '☁️',
            '04d' => '☁️',  // broken clouds
            '04n' => '☁️',
            '09d' => '🌧️',  // shower rain
            '09n' => '🌧️',
            '10d' => '🌦️',  // rain day
            '10n' => '🌧️',  // rain night
            '11d' => '⛈️',  // thunderstorm
            '11n' => '⛈️',
            '13d' => '❄️',  // snow
            '13n' => '❄️',
            '50d' => '🌫️',  // mist
            '50n' => '🌫️',
        ];
        return $iconMap[$iconCode] ?? '🌤️';
    }

    /**
     * Date mock pentru fallback
     */
    private function getMockWeather(): array
    {
        return [
            'location' => self::LOCATION['name'],
            'temperature' => 22,
            'feelsLike' => 20,
            'description' => 'parțial înnorat',
            'icon' => '⛅',
            'humidity' => 65,
            'pressure' => 1013,
            'windSpeed' => 3.5,
            'cloudiness' => 40,
            'sunrise' => now()->setHour(6)->toIso8601String(),
            'sunset' => now()->setHour(20)->toIso8601String(),
            'cached_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Curăță cache-ul manual (pentru debugging/testing)
     */
    public function clearCache(): JsonResponse
    {
        Cache::forget(self::CACHE_KEY);
        
        return response()->json([
            'message' => 'Cache-ul vremii a fost curățat'
        ]);
    }
}

