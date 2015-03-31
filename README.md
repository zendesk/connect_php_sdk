# Outbound PHP Library

## Installation
### Clone

    git clone https://github.com/outboundio/lib-php.git

### Composer

    "require": {
        ...
        "outbound/outbound-php" : "1.*"
        ...
    }

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
- Some times you don't have a user id yet for a user but you still want to identify them and trigger events for them. You can do this by generating a new ID (call this the anonymous ID) and identify the user as you normally would. Then, once the user becomes a real, identifiable user and you have a real ID for them, make another identify call, this time pass in the anonymous ID as the previous ID.

        Outbound::identify(
            newUserId,
            array('previous_id' => anonymousId),
            array(... attributes here ...),
        )

### Groups
You can create a set of attributes and have them be inherited by a group of users. This can all be done with the `identify` call.

    user_info = array(
        'group_id' => identifier for the group,
        'group_attributes' => array(
            ... any attributes you want to share about all members of the group ...
        )
    )
    Outbound::identify(
        user_id,
        user_info,
        array( ... user specific attributes here ...),
    )

- Group IDs are treated just like user IDs. They should only be strings or numbers.
- Users in a group will inherit group attributes but user attributes take precedences. So if there is an attribute `state` set on the group and it is set to "California" and there is also a `state` attribute set on the user but set to "New York", the value for that user is "New York". If the user didn't have that attribute, the value of `state` for that user would be the group value which is "California".
- You only need to pass in the group attributes when they are initially set or when they are updated but you do need to set the group id for each user you want to be in the group.

### Event Name
- An event name in a track can only be a string. Any other type of value will throw an exception and the call will not be sent to Outbound.
- Event names can be anything you want them to be (as long as they are strings) and contain any character you want.

### Identify call
- All user info params (the second parameter) are optional. The keys shown in the example are the only ones supported. All except `apns` and `gcm` need to be strings. `apns` and `gcm` should be arrays of strings.
- User attributes (the third parameter) is also optional. This is a free form array of key/value pairs of attributes of the user you wish to track.
