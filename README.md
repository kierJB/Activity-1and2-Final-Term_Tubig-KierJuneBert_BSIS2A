 <mark>REMINDER:</mark> If you want to see, in a good format visualization (Just click " CTRL + SHIFT + V " )



_____________________________________________________________________________________________________________
## ACTIVITY #1 : Create Sign In and Register Page Of Your Website (Any Website)
_____________________________________________________________________________________________________________

***Resident Records System***
- Login/Sign In
- Register

***User Table***
- id -> int(pk) (6 columns)
- username -> varchar (50)
- password -> varchar (255)
- role -> Enum('admin',''staff')
- status -> Enum('pending',''active','disable')
- created_at -> TimeStamp

***Resident Table (9 column)***
- id -> int(pk)
- first_name -> varchar(100)
- last_name -> varchar(100)
- birth_date -> date
- sex -> enum('male','female')
- civil_status -> enum('single','married','widowed','seperated')
- street -> VARCHAR(50)
- created_by -> int, foreign key -> uses.id
- created_at -> TimeStamp > on ipdate current timestamp

_____________________________________________________________________________________________________________
## ACTIVITY #2 : Apply the access control, User management,and Crud Operations to your activity #1 website. 	
_____________________________________________________________________________________________________________

Mga page na idaragdag:

### First to do — `logout.php`

- Tatapusin ang login session ng user at ibabalik siya sa sign-in page.

### Second to do — `users.php`

- Para sa admin na mamahala ng user accounts.
- Tingnan ang users, aprubahan o i-disable ang account, at baguhin ang role.

### Third to do — `residents.php`

- Katumbas ng `products.php` ni Ma’am.
- Dito gagawin ang CRUD ng resident records: Add, View, Update, Delete.

***Access control:***
- Admin lang ang makakagamit ng users.php.
- Admin at active staff ang makakagamit ng residents.php.
- Pending o disabled na account ay hindi makakapasok sa system.





