#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------

#------------------------------------------------------------
# Table: Users
#------------------------------------------------------------

CREATE TABLE Users(
        id        Int  Auto_increment  NOT NULL ,
        login       Varchar (255) NOT NULL ,
        password    Varchar (255) NOT NULL ,
        mail      Varchar (255) NOT NULL
	,CONSTRAINT Users_PK PRIMARY KEY (id)
)ENGINE=InnoDB;
