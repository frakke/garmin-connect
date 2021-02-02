<div>
	<form wire:submit.prevent="garminFormSubmit" action="/contact" method="POST" class="w-full">
    	@csrf
    	<label for="sessionId">SESSIONID</label>
    	<input wire:model="sessionId" id="sessionId" class="" type="text" placeholder="SESSIONID" name="name" value="{{ old('sessionId') }}" />
    	<label for="garminSsoGuid">GARMIN-SSO-GUID</label>
    	<input wire:model="garminSsoGuid" id="garminSsoGuid" class="" type="text" placeholder="GARMIN-SSO-GUID" name="name" value="{{ old('garminSsoGuid') }}" />
    	<label for="garminSsoCustGuid">GARMIN-SSO-CUST-GUI</label>
    	<input wire:model="garminSsoCustGuid" id="garminSsoCustGuid" class="" type="text" placeholder="GARMIN-SSO-CUST-GUID" name="name" value="{{ old('garminSsoCustGuid') }}" />

    	<button class="flex px-6 py-3 text-white bg-indigo-500 rounded-md hover:bg-indigo-600 hover:text-white focus:outline-none focus:shadow-outline focus:border-indigo-300" type="submit">
            <span class="self-center float-left ml-3 text-base font-medium">Submit</span>
        </button>
	</form>
	<p>{{ $message }}</p>
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
