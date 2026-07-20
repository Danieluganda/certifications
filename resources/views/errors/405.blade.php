@include('errors.minimal', [
    'code' => '405',
    'title' => 'Action not available',
    'heading' => 'That link cannot be opened this way',
    'message' => 'Some study actions must be submitted from inside the app so they include the right form details.',
])
