## API

WITHOUT BEARER TOKEN--------------------------------
http://127.0.0.1:8000/api/auth/register

-   name
-   email
-   password
-   npp
-   npp_supervisor

http://127.0.0.1:8000/api/auth/login

-   email
-   password

WITH BEARER TOKEN------------------------------------
http://127.0.0.1:8000/api/auth/logout

http://127.0.0.1:8000/api/epresence/epresence

-   type
-   waktu

http://127.0.0.1:8000/api/epresence/history

http://127.0.0.1:8000/api/epresence/approve/id
