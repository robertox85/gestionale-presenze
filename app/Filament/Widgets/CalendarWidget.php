<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\FestivitaResource;
use App\Models\Evento;
use App\Models\Festivita;
use App\Models\Sede;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public string|null|Model $model = Festivita::class;

    public $countryCode = null;

    // protected static string $view = 'filament.widgets.calendar-widget';
    public function eventDidMount(): string
    {
        return <<<JS
function({ event, el }) {
    function formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'numeric',
            year: 'numeric'
        });
    }
    // Costruisce il contenuto del tooltip con tutti i dettagli
    const tooltipContent = 
        '<div class="p-4 max-w-sm bg-white rounded shadow-lg">' +
            '<div class="text-sm text-gray-600 mb-3">' +
                '<div class="text-lg font-semibold mb-3">' + event.title + '</div>' +
                '<div class="mb-1">' + formatDate(event.start) + '</div>' +
            '</div>' +
            
 
        '</div>';
     // Applica il tooltip con configurazione per HTML
    el.setAttribute("x-tooltip", "tooltip");
    el.setAttribute("x-data", "{ tooltip: { content: '" + tooltipContent + "', allowHTML: true } }");
    
    
}
JS;
    }

    protected function headerActions(): array
    {
        $sedi = Sede::all();
        $sediOptions = $sedi->map(fn($sede) => [
            'id' => $sede->id,
            'nome' => $sede->nome,
        ])->toArray();
        return [
            ...parent::headerActions(),
            Action::make('filterSede')
                ->label('Filtra per sede')
                ->form([
                    Select::make('sede_id')
                        ->label('Sede')
                        ->options(
                            Sede::pluck('nome', 'id')->toArray() + ['' => 'Tutte le sedi']
                        )
                        ->default('')
                ])
                ->action(function (array $data): void {
                    $this->filterBySede($data['sede_id']);
                })
        ];
    }


    public function filterBySede($sedeId)
    {
        $this->countryCode = Sede::find($sedeId)->country_code;
        $this->refreshRecords();
    }
    public function fetchEvents(array $info): array
    {

        $query = Festivita::query()
            ->where('data_festivita', '>=', $info['start'])
            ->where('data_festivita', '<=', $info['end']);

        if ($this->countryCode) {
            $query->where('country_code', $this->countryCode);
        }

        return $query->get()
            ->map(
                fn(Festivita $festivita) => [
                    'title' => $festivita->descrizione,
                    'start' => $festivita->data_festivita,
                    'end' => $festivita->data_festivita,
                    'url' => FestivitaResource::getUrl(name: 'edit', parameters: ['record' => $festivita]),
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }


}
