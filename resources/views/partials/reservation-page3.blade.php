<!-- Page 3: Review and Submit -->
<div id="page3" class="form-page">
    <div class="mt-6 p-4 border-2 border-gray-700 rounded-md">        <div class="flex flex-row gap-2">
            <div class="w-1/2">
                <label class="block text-gray-700 font-medium mb-2">Name (Lastname, Firstname)</label>
                <input id="name" name="name" class="w-full px-2 py-2 border-2 border-gray-400 rounded-md bg-gray-100 mb-4" required>
            </div>
            <div class="w-1/2">
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-2 py-2 border-2 border-gray-400 rounded-md bg-gray-100 mb-4" required>
            </div>
        </div>
        <label class="block text-gray-700 font-medium mb-2">Name of organization / company / college / office / unit</label>
        <input id="organization" name="organization" class="w-3/4 px-2 py-2 border-2 border-gray-400 rounded-md bg-gray-100 mb-4" required>

        <!-- Signature Upload section -->
        <div class="signature-container">
            <label class="block text-gray-700 font-medium mb-2">Signature Upload</label>
            <div class="signature-upload-container">
                <input type="file" id="signature-upload" name="signature" accept="image/*" class="w-full px-3 py-2 border-2 border-gray-400 rounded-md bg-white">
                <p class="text-sm text-gray-600 mt-2">Please upload an image file of your signature (JPG, PNG, GIF)</p>
                <div id="signature-preview" class="mt-3 hidden">
                    <p class="text-sm text-gray-700 mb-2">Preview:</p>
                    <img id="signature-preview-img" src="" alt="Signature Preview" class="max-w-xs max-h-32 border border-gray-300 rounded">
                </div>
            </div>
        </div>    </div>
    
    <!-- Single, clear submit button -->
    <div class="mt-6 text-center">
        <button type="submit" id="final-submit-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg">
            Submit Reservation Request
        </button>
    </div>
</div>
