## API

WITHOUT BEARER TOKEN--------------------------------
<br>
1. http://127.0.0.1:8000/api/auth/register

-   name
-   email
-   password
-   npp
-   npp_supervisor

2. http://127.0.0.1:8000/api/auth/login

-   email
-   password

WITH BEARER TOKEN------------------------------------
<br>
3. http://127.0.0.1:8000/api/auth/logout

4.http://127.0.0.1:8000/api/epresence/epresence

-   type
-   waktu

5.http://127.0.0.1:8000/api/epresence/history

6.http://127.0.0.1:8000/api/epresence/approve/id
