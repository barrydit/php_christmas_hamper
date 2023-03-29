# php_christmas_hamper
Donated / Volunteer Christmas Hamper for PHP. Self-installs

Its still a project in progress. But I thought I would share it by uploading it to github.
The image is a bit dated, but it goes to show how the web application works.

I have added additional code for composer to unpack and execute an update command, there by creating /vendor and the associative libraries.

I would like to further add some code by adding the ability to add github oauth token to the auth.json file on the clients computer. The login details are root : password

I added some new features this round, such as 
  Better management of config and loading php files.
  Session managing. PHP was randomly kicking out and loss of POST information. So this is a trial / experiment.
  Hamper Recovery. If the client loses the hamper_id in the clients table, it uses a internal query to find it via hampers client_id. Simple save/fixes it.
  
  In the video I had to update my database with bday_date point of reference, so all the bdays were all off.

Recent/Current design

![php_christmas_hamper-2](https://user-images.githubusercontent.com/6217010/228450154-130751f9-54e4-4081-bcba-f54cd358f4a9.gif)


Previous video

![x-mas-hamper](https://user-images.githubusercontent.com/6217010/214609801-8e2ce2c6-28a1-4e52-9c4f-e9cae5c2be5e.gif)
