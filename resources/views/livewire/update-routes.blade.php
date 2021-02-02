<div class="max-w-lg bg-white shadow-md rounded-lg overflow-hidden mx-auto lg:my-4">
    <div class="divide-y divide-gray-200 py-4 px-8 mt-3">
		<form wire:submit.prevent="garminFormSubmit" action="/contact" method="POST" class="w-full">
	    	@csrf
			<h2 class="text-gray-700 font-semibold text-2xl tracking-wide mb-2">Update routes data</h2>
    		
    		<div class="py-4 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">

		    	<div class="flex flex-col">
			    	<label for="sessionId" class="leading-loose">SESSIONID</label>
			    	<input wire:model="sessionId" id="sessionId" class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600" type="text" placeholder="SESSIONID" name="name" value="{{ old('sessionId') }}" />
			    </div>
			    <div class="flex flex-col">
			    	<label for="garminSsoGuid" class="leading-loose">GARMIN-SSO-GUID</label>
			    	<input wire:model="garminSsoGuid" id="garminSsoGuid" class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600" type="text" placeholder="GARMIN-SSO-GUID" name="name" value="{{ old('garminSsoGuid') }}" />
			    </div>
			    <div class="flex flex-col">
			    	<label for="garminSsoCustGuid" class="leading-loose">GARMIN-SSO-CUST-GUI</label>
			    	<input wire:model="garminSsoCustGuid" id="garminSsoCustGuid" class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600" type="text" placeholder="GARMIN-SSO-CUST-GUID" name="name" value="{{ old('garminSsoCustGuid') }}" />
			    </div>

		    	<button class="uppercase px-8 py-2 rounded-full bg-green-500 text-blue-50 max-w-max shadow-sm hover:shadow-lg" type="submit">
		            Submit
		        </button>
		    </div>
		</form>
		<div class="pt-4 text-sm">
			<p>{{ $message }}</p>
		</div>
	</div>
</div>

<pre>
var cookiesRaw = decodeURIComponent(document.cookie);
cookiesRaw = cookiesRaw.split(';');

var cookies = [];

for (i = 0; i < cookiesRaw.length; i++) {
	var items = cookiesRaw[i].split('=');
  	
  	cookies[items[0].trim()] = items[1];
}

console.error('SESSIONID', cookies['SESSIONID']);
console.error('GARMIN-SSO-GUID', cookies['GARMIN-SSO-GUID']);
console.error('GARMIN-SSO', cookies['GARMIN-SSO']);
console.error('GARMIN-SSO-CUST-GUID', cookies['GARMIN-SSO-CUST-GUID']);
</pre>
