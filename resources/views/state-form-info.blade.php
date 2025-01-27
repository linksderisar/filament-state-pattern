<div>
    @if( $state)
        <div class="grid gap-4 items-center grid-cols-11 ">

            <div class="flex flex-col gap-2 justify-between border p-2 bg-gray-100 rounded-lg col-span-5">
                <span>State change from:</span>
                <span class="font-bold">
                    @if(method_exists($state, 'getLabel'))
                        {{$state::getLabel()}}
                        @else
                        {{  \Illuminate\Support\Str::headline($state)}}
                    @endif
          </span>
            </div>

            <div class="flex justify-center col-span-1">-></div>

            <div class="flex flex-col gap-2 justify-between border p-2 bg-gray-100 rounded-lg col-span-5">
                <span>To:</span>
                @if($toState )
                    <span class="font-bold"> @if(method_exists($toState, 'getLabel'))
                            {{$toState::getLabel()}}
                        @else
                            {{  \Illuminate\Support\Str::headline($toState)}}
                        @endif</span>
                @else
                    <span class="font-bold">-</span>

                @endif
            </div>

        </div>
        @if( is_subclass_of($transitionClass, \Linksderisar\FilamentStatePattern\Classes\TransitionWithFilamentSupport::class) && $transitionClass::filamentStateFormDescription($record) )
            <div class="flex  gap-4 items-center  border p-2 rounded-lg mt-4 px-4">
                <div>
                    <x-heroicon-c-exclamation-circle class="w-6 h-6 text-warning-500"></x-heroicon-c-exclamation-circle>
                </div>
                <div class="flex flex-col gap-2">
                    <p class="font-bold">Important note:</p>
                    {{$transitionClass::filamentStateFormDescription($record)}}
                </div>
            </div>
        @endif

    @endif
</div>
