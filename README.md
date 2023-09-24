# Please find below a task for PHP proficiency evaluation:
The project should consist of at least the following 3 files: 

- index1.html
- index2.html
- banner.php

# 1 MySQL table with the next mandatory columns:

- ip_address
- user_agent
- view_date
- page_url
- views_count


  # index1.html and index2.html 
  
Should have an image tag that inserts some image into the page using banner.php file: 
`<img src="banner.php">`

  Every time the image is loaded, the page visitor's info should be recorded in the MySQL table:


- IP address of the visitor (ip_address column)
- Their user-agent (user_agent column)
- The date and time the image was shown for this visitor (view_date column)
- URL of the page where the image was loaded (page_url column)
- Number of image loads for the same visitor (views_count column) - conditions are described below.
  If a user with the same IP address, user-agent, and page URL hits the page again,
the view_date column has to be updated with the current date and time, as well as views_count column has to be increased by 1.