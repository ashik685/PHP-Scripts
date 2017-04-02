<?php

/*
Script Name : WP Password Updater
Author : Syed Ashik Mahmud
Version : 1.0
Author URL : http://aaextensions.com
*/

?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title> WP Password Updater </title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    
    <style type="text/css">
	
	.password_updater_area {		
		 margin-top:75px;
	}
	
	.database_connection_form{
	   margin-top:45px;	
	}
	
	.title{
	  margin-bottom:15px;	
	}
	
	.btn-lg{
	 	 cursor: pointer;	
	}
	
	
	
	</style>
    
    
    
  </head>
  <body>
    
    <div class="password_updater_area">
    	<div class="container">
    		<div class="row">
    			<div class="col-lg-12">
                
                  <center><h2 class="title"> Wordpress Password Updater</h2> </center>
                  
                  <div class="database_connection_form">
                  
                                   
                  <form action="" method="post">
                  	 <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">DB Host</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="host" value="" id="example-text-input" aria-describedby="hostHelp" placeholder="Enter Hostname">
                         <small id="hostHelp" class="form-text text-muted">Normally we get it as : localhost</small>
                      </div>
                      </div>
                      
                      <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">DB Username</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="db_user" value="" id="example-text-input" aria-describedby="hostHelp" placeholder="Enter DB User">
                        <small id="hostHelp" class="form-text text-muted">Normally we get it as : root</small>
                      </div>
                     </div>
                     
                     
                     <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">DB Password</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="db_pass" value="" id="example-text-input">
                      </div>
                     </div>
                     
                     
                     <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">DB NAME</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="db_name" value="" id="example-text-input">
                      </div>
                     </div>
                     
                     
                     <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">User Table Extension</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="table_extension" value="" id="example-text-input" aria-describedby="hostHelp" placeholder="Enter User Table Extension ">
                        <small id="hostHelp" class="form-text text-muted">Normally we get it as : wp</small>
                      </div>
                     </div>
                     
                      <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">New Password</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="user_pass" value="" id="example-text-input" aria-describedby="hostHelp" placeholder="Enter New Pass">
                        <small id="hostHelp" class="form-text text-muted">You must use here md5. You can do it here : http://www.md5.cz/</small>
                      </div>
                     </div>
                     
                     
                      <div class="form-group row">
                      <label for="example-text-input" class="col-2 col-form-label">User Id</label>
                      <div class="col-10">
                        <input class="form-control" type="text" name="user_id" value="" id="example-text-input" aria-describedby="hostHelp" placeholder="Enter User Id">
                        <small id="hostHelp" class="form-text text-muted">You must need to know user id. You can know that from our WP User list Form.</small>
                      </div>
                     </div>
                     
                     
                     <div class="form-group row">
                      <div class="col-12 text-center">
                        <input class="btn btn-success btn-lg" type="submit" value="Update !! " name="submit"/>
                        
                      </div>
                     </div>  

									 
                    
                  </form>
                  
                  
                    
                   
                  
                  
                  </div>
                  
                  
				 <?php
									  
					if(isset($_POST['submit'])){
										 
						if( $_POST['host'] != '' && $_POST['db_user'] != ''   && $_POST['db_name'] != '' && $_POST['table_extension'] != '' && $_POST['user_pass'] != '' && $_POST['user_id'] != ''){
							
							
							$host = $_POST['host'];
							$db_user = $_POST['db_user'];
							$db_pass = $_POST['db_pass'];
							$db_name = $_POST['db_name'];
							$table_extension = $_POST['table_extension'];
							$user_pass = $_POST['user_pass'];
							$user_id = $_POST['user_id'];
							
							
                    
						$link = mysqli_connect ("$host","$db_user","$db_pass","$db_name");
						
						if(mysqli_connect_error()){							
							die("Database connection has a problem");
							}
							
							
						
						
                    
                    //$query = 'SELECT * FROM '.$table_extension.'_users';
					
					//$query = "UPDATE" .$table_extension.'_users'." SET user_pass=".$user_pass."' WHERE id=".$user_id;
					
					$query = "UPDATE ".$table_extension.'_users'." SET user_pass='".$user_pass."' WHERE id=".$user_id."";

					if ($link->query($query) === TRUE) {
						echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						  <strong>Success!</strong> Pass Updated.
						</div>';
					} else {
						echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						  <strong>Warning!</strong> Error in update !!
						</div>' . $link->error;
					}
										
                   /* if($result=mysqli_query($link,$query)){
                        
                        //$row = mysqli_fetch_array($result);	
                        //print_r($row);
                        
                        if(mysqli_num_rows($result) > 0){
                            
                             while($row = mysqli_fetch_assoc($result)) {
                                 
                                 echo 'User Login : ' . $row["user_login"].'<br/>';
                                 echo 'User Pass : ' . $row["user_pass"].'<br/>';
                                 echo 'User Email : ' . $row["user_email"].'<br/><br/>';
                                 
                             }
                            
                            }
                        
                        } */
                            
                        
                    $link -> close();
					
					}
					
					else{
					 echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
						  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						  </button>
						  <strong>Warning!</strong> Better check form, you miss something.
						</div>';
					}
					
				}
				
				
				
                    
                    ?>
                  
                       <div class="form-group" class="text-center">
									

					<a href="?delete=1" class="btn btn-danger">Delete Script </a>

					<?php
						if(isset($_GET['delete']))
						{
							unlink(__FILE__);
						}
					?>


					</div>	
                
                </div>
    		</div>
    	</div>
    
    
    </div>

    <!-- jQuery first, then Tether, then Bootstrap JS. -->
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
  </body>
</html>