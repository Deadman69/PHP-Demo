#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------

#------------------------------------------------------------
# Table: Users
#------------------------------------------------------------

CREATE TABLE Users(
        id        Int  Auto_increment  NOT NULL ,
        nom       Varchar (255) NOT NULL ,
        prenom    Varchar (255) NOT NULL ,
        mail      Varchar (255) NOT NULL ,
        telephone Varchar (255) NOT NULL ,
        login     Varchar (255) NOT NULL ,
        password  Varchar (255) NOT NULL
	,CONSTRAINT Users_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Crypto
#------------------------------------------------------------

CREATE TABLE Crypto(
        uid       Int NOT NULL ,
        iv        Binary (16) NOT NULL ,
        cle       Binary (16) NOT NULL
	,CONSTRAINT Crypto_PK PRIMARY KEY (uid)

	,CONSTRAINT Crypto_Users_FK FOREIGN KEY (uid) REFERENCES Users(id)
)ENGINE=InnoDB;
