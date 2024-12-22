<?php
require 'includes/db_connect.php';
                                $password = "admin1234";
                                $hash = password_hash($password, PASSWORD_DEFAULT);
                                $ID = 80085;

                                // Insert into database
                                $sql = "INSERT INTO admin(User_ID, password) VALUES ('$ID', '$hash')";
                                    mysqli_query($conn, $sql);
                        ?>