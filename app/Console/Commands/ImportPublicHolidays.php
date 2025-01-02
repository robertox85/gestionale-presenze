<?php

namespace App\Console\Commands;

use App\Models\Festivita;
use App\Models\Sede;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use om\IcalParser;

class ImportPublicHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:import-google {countryCode} {url}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa festività pubbliche da Google Calendar';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $countryCode = $this->argument('countryCode');
        $url = $this->argument('url');

        $this->info("Fetching holidays for {$countryCode} from Google Calendar...");

        try {


            $sedi = Sede::where('country_code', $countryCode)->get();
            if ($sedi->isEmpty()) {
                $this->error("No offices found for country code {$countryCode}");
                return 1;
            }

            $client = new Client();
            $response = $client->get($url, [
                'verify' => false, // Imposta a `true` in produzione con un certificato valido
            ]);

            $icalContent = $response->getBody()->getContents();

            $ical = new IcalParser();
            $results = $ical->parseString($icalContent);

            foreach ($ical->getEvents()->sorted() as $event) {
                // Converte la data in un oggetto DateTime per facilitarne la manipolazione
                $startDate = $event['DTSTART'];
                $endDate = $event['DTEND'] ?? $startDate;
                $description = $event['SUMMARY'];
                // Verifica che la data sia nell'anno 2025
                if ($startDate->format('Y') === '2025' || $endDate->format('Y') === '2025') {
                    Log::info('Processing event', $event);

                    foreach ($sedi as $sede) {
                        Festivita::updateOrCreate(
                            [
                                'country_code' => $countryCode,
                                'data_festivita' => $startDate->format('Y-m-d'), // Salva in formato 'Y-m-d'
                            ],
                            [
                                'descrizione' => $description,
                                'sede_id' => $sede->id,
                            ]
                        );
                    }
                }

            }

            $this->info('Holidays imported successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            Log::log('error', 'An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
