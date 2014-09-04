# Outbound PHP Library

## Setup

    require_once('lib/outbound.php');
    Outbound::init('YOUR_API_KEY');

## Identify User

    Outbound::identify(
        'USER_ID',
        array(
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email => 'user@domain.com',
            'phone_number' => '5551234567',
            'apns' => array('ios device token'],
            'gcm' => array('android device token']
        ),
        array(
            'some_custom_attriute' => 'Loren Ipsum',
        )
    );

## Track Event

    Outbound::track(
        'USER_ID',
        'EVENT NAME',
        array(
            'eventAttr' => '',
        )
    )

## Specifics
### User ID
- A user ID must ALWAYS be a string or a number. Anything else will throw an exception and the call will not be sent to Outbound. User IDs are always stored as strings. Keep this in mind if you have different types. A user with ID of 1 (the number) will be considered the same as user with ID of "1" (the string).
- A user ID should be static. It should be the same value you use to identify the user in your own system.

### Event Name
- An event name in a track can only be a string. Any other type of value will throw an exception and the call will not be sent to Outbound.
- Event names can be anything you want them to be (as long as they are strings) and contain any character you want.

### Identify call
- All user info params (the second parameter) are optional. The keys shown in the example are the only ones supported. All except `apns` and `gcm` need to be strings. `apns` and `gcm` should be arrays of strings.
- User attributes (the third parameter) is also optional. This is a free form array of key/value pairs of attributes of the user you wish to track.
