@include('errors.minimal', [
    'code' => '504',
    'title' => 'Request timed out',
    'heading' => 'That request took too long',
    'message' => 'Please try again. If it keeps happening, use a smaller action or come back shortly.',
])
