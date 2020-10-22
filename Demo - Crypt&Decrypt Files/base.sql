#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------

#------------------------------------------------------------
# Table: Files
#------------------------------------------------------------

CREATE TABLE Files(
        id              Int  Auto_increment  NOT NULL ,
        file_path       Varchar (255) NOT NULL 
	,CONSTRAINT Files_PK PRIMARY KEY (id)
)ENGINE=InnoDB;

#------------------------------------------------------------
# Table: Crypto
#------------------------------------------------------------

CREATE TABLE Crypto(
        uid       Int NOT NULL ,
        iv        Binary (16) NOT NULL ,
        cle       Binary (16) NOT NULL
	,CONSTRAINT Crypto_PK PRIMARY KEY (uid)

	,CONSTRAINT Crypto_Files_FK FOREIGN KEY (uid) REFERENCES Files(id)
)ENGINE=InnoDB;