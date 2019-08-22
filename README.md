# Find-it Coding Challenge

### Site URL:
https://findly.herokuapp.com

### Process to run this application locally
1. Clone the app
2. Install all dependencies by running:
    ```composer install```
3. After installing all dependencies, just run the server:
    ```php artisan serve```
4. Then use your preferred browser and type: "__http://localhost:8000__"
5. Follow the process presented on the page, and enjoy!

### Tests
I used the Laravel built-in PHPUnit to test the API endpoints.
All tests are stored in "__tests__" directory.

### Search
I allowed the searching query to have optional parameters __latitude__ and __longitude__ to improve relative search results score.
But once not provided, the searching algorithm will simply sort city values in alphabetic order.

### Score
For scores, I relied on distance between the center position provided by the client and retrieved cities' positions.
To generate the score I used a reverse normalization technique on the distances.