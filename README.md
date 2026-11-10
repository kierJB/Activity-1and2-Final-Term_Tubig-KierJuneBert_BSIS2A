 <mark>REMINDER:</mark> If you want to see, in a good format visualization (Just click " CTRL + SHIFT + V " )

_____________________________________________________________________________________________________________
## DEFAULT PASSWORD FOR ADMIN

Email: admin <br>
Pass: admin123

_____________________________________________________________________________________________________________
## ACTIVITY #1 : Create Sign In and Register Page Of Your Website (Any Website)

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

### First to do — `logout.php`

- Tinatapos ang kasalukuyang user session.
- Ibinalik ang user sa sign-in page.

### Second to do — `users.php`

- Admin lang ang may access sa page na ito.
- Ipinapakita ang mga registered user accounts.
- Maaaring i-activate, i-disable, baguhin ang role, o i-delete ng admin ang user account.
- Pending o disabled na account ay hindi makakapasok sa system.

### Third to do — `residents.php`

- Admin at active staff lang ang may access sa page na ito.
- Dito maaaring mag-Add, View, Update, at Delete ng resident records.
- Ginagamit ang kasalukuyang resident fields: first name, last name, birth date, sex, civil status, at street.
- Itinatala rin ang user na gumawa ng record at ang petsa ng paggawa nito.

### Access Control

- Admin lang ang makakagamit ng `users.php`.
- Admin at active staff ang makakagamit ng `residents.php`.
- Ang pending o disabled na account ay hindi makakapasok sa system.
- Bine-verify ng system ang user session, role, at account status sa protected pages.

### Database at Security

- Kumokonekta ang system sa `baranggaydb` database at gumagamit ng `users` at `resident` tables.
- Gumagamit ng prepared statements ang database queries.
- Naka-hash ang mga password at vine-verify ito sa sign-in.
- Pare-pareho ang internal CSS style ng mga page.



