## Nasdaq User Authentication via Auth Toeken

This is a Drupal 9 custom module that adds a Auth Token field to the user entity and generates a 32 character unique auth token for each user.

If the user provides the correct auth token in the query string as https://drupal9.lndo.site?authtoken=[AUTHTOKEN], the user get logged in.
