<div style="text-align: center">
    <button wire:click="fastest1km" class="uppercase px-8 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">PR 1km</button>
    <button wire:click="fastest5km" class="uppercase px-8 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">PR 5km</button>
    <button wire:click="fastest10km" class="uppercase px-8 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">PR 10km</button>
    <button wire:click="fastest21km" class="uppercase px-8 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">PR 21km</button>
    <h1>{{ $speed }}</h1>
</div>