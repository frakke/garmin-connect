<div style="text-align: center">
    <button wire:click="fastest1km" class="underline text-gray-900 dark:text-white bg-gray-200 p-6 sm:rounded-lg">PR 1km</button>
    <button wire:click="fastest5km" class="underline text-gray-900 dark:text-white bg-gray-200 p-6 sm:rounded-lg">PR 5km</button>
    <button wire:click="fastest10km" class="underline text-gray-900 dark:text-white bg-gray-200 p-6 sm:rounded-lg">PR 10km</button>
    <button wire:click="fastest21km" class="underline text-gray-900 dark:text-white bg-gray-200 p-6 sm:rounded-lg">PR 21km</button>
    <h1>{{ $speed }}</h1>
</div>