<?php

namespace Linksderisar\FilamentStatePattern\Services;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Linksderisar\FilamentStatePattern\Classes\TransitionWithFilamentSupport;

/**
 * @property string $modelStateClass
 */
class FilamentStateChangingActionConfigurator
{
    public function __construct(
        private readonly Action|\Filament\Actions\Action $action,
        private readonly string $modelStateAttribute,
        private readonly string $modelStateClass,
        private readonly string $model,
    ) {}

    public function getAction(): Action|\Filament\Actions\Action
    {

        return $this->action->icon('heroicon-o-adjustments-vertical')
            ->action(function ($record, array $data): void {
                // Transition to new state after the modal is submitted
                $newState = $data['toState'];
                if ($newState) {
                    $record->{$this->modelStateAttribute}->transitionTo($newState, ...Arr::except($data, ['toState']));
                }
            })->visible(function ($record) {
                // check if we can transition to anywhere
                $stateCollection = $this->model::getStates()[$this->modelStateAttribute];

                foreach ($stateCollection as $state) {
                    if ($record->{$this->modelStateAttribute}->canTransitionTo($state)) {
                        return true;
                    }
                }

                return false;
            })
            ->form(function () {
                return [
                    // Select new state
                    Select::make('toState')
                        ->required()
                        ->label('Change state to')
                        ->reactive()
                        ->afterStateUpdated(function ($component, $state, $record) {
                            if (! $state) {
                                return;
                            }
                            // After the state is updated, fill the form with default values from transition class
                            /** @var TransitionWithFilamentSupport $transitionClass */
                            $transitionClass = $this->modelStateClass::config()
                                ->resolveTransitionClass($record->{$this->modelStateAttribute}, $state);

                            // Since filament has some trouble with default values, we used a different approach
                            // By providing a static method to fill the form with default values from transition class
                            if ($transitionClass && is_subclass_of($transitionClass, TransitionWithFilamentSupport::class)) {
                                $component->getContainer()->fill([
                                    'toState' => $state,
                                    ...$transitionClass::fillFilamentFormWithDefaultValues($record),
                                ]);
                            }
                        })
                        ->options(function ($record) {
                            // Get all possible states that the record can transition to
                            $stateCollection = $this->model::getStates()[$this->modelStateAttribute];

                            // Filter out the current state, so it won't be an option for states that allow all transitions
                            $stateCollection = $stateCollection
                                ->filter(function ($state) use ($record) {
                                    return $record->{$this->modelStateAttribute}->canTransitionTo($state) && $state !== get_class($record->{$this->modelStateAttribute});
                                })
                                ->filter(function ($state) use ($record) {
                                    /** @var TransitionWithFilamentSupport $transitionClass */
                                    $transitionClass = $this->modelStateClass::config()
                                        ->resolveTransitionClass($record->{$this->modelStateAttribute}, $state);
                                    if ($transitionClass && is_subclass_of($transitionClass, TransitionWithFilamentSupport::class)) {
                                        return $transitionClass::canTransitionFromFilament($record);
                                    }
                                    return true;
                                });

                            return $stateCollection->mapWithKeys(function ($state) {
                                return [$state => method_exists($state, 'getLabel') ? $state::getLabel() : Str::headline($state)];
                            })->toArray();
                        }),

                    // A small informational view about the state changes and the transition
                    Placeholder::make('state_info')
                        ->label('')
                        ->content(function ($record, $get) {
                            $transitionClass = '';

                            // If the toState is set, we can resolve the transition class
                            if ($get('toState')) {
                                $transitionClass = $this->modelStateClass::config()
                                    ->resolveTransitionClass($record->{$this->modelStateAttribute}, $get('toState'));
                            }

                            return view('ldi-fsp::state-form-info', [
                                'record' => $record,
                                'state' => $record->{$this->modelStateAttribute},
                                'toState' => $get('toState'),
                                'transitionClass' => $transitionClass,
                            ]);
                        }),

                    //Pull additional fields from the state
                    Grid::make(2)->schema(function ($get, $record) {

                        if (! $get('toState')) {
                            return [];
                        }
                        // Sometimes the transition class is not available. Because we
                        // allow all transitions. In that case, we don't need to show any additional fields
                        $transitionClass = $this->modelStateClass::config()
                            ->resolveTransitionClass($record->{$this->modelStateAttribute}, $get('toState'));

                        if ($transitionClass && method_exists($transitionClass, 'filamentFields')) {
                            return $transitionClass ? $transitionClass::filamentFields($record) : [];
                        }

                        return [];
                    }),
                ];
            });
    }
}
