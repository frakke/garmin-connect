<div class="max-w-lg bg-white shadow-md rounded-lg overflow-hidden mx-auto lg:my-4">
	<div class="relative px-4 py-10 bg-white mx-8 md:mx-0 sm:p-10">
		<h2 class="text-gray-700 font-semibold text-2xl tracking-wide mb-4">Fastest stretches</h2>
		<div style="text-align: center">
		    <button wire:click="fastest1km" class="uppercase px-6 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">1 km</button>
		    <button wire:click="fastest5km" class="uppercase px-6 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">5 km</button>
		    <button wire:click="fastest10km" class="uppercase px-6 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">10 km</button>
		    <button wire:click="fastest21km" class="uppercase px-6 py-2 rounded-full bg-yellow-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg">21.1 km</button>
		    <div class="pt-4 text-lg">
		    	<p>{{ $speed }}</p>
		    </div>
		</div>
	</div>
</div>