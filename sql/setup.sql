-- IMPORTANT NOTE:
-- DIALECT: MySQL/Maria
-- collate=latin1_german1_ci might not be required and/or may work with your given database

-- Table for the users
create table tbl_users
(
    id int auto_increment
        primary key,
    vorname text not null,
    nachname text not null,
    benutzername text not null,
    geburtsdatum date not null,
    passwort text not null,
    ist_aktiv tinyint(1) not null,
    jahrgang int not null,
    klasse text not null,
    datum_erstellt date not null,
    datum_letzte_anderung date not null,
    kann_reset_anfordern tinyint(1) not null,
    ist_admin tinyint(1) not null
)
    collate=latin1_german1_ci;

-- Table of all the elections
create table tbl_sportwahl
(
    id int auto_increment
        primary key,
    name_wahl text not null,
    beschreibung text not null,
    ist_aktiv tinyint(1) not null,
    datum_erstellt date not null,
    datum_beginn date not null,
    datum_ende date not null,
    erstellt_von int not null,
    pdf_pfad text not null,
    anzahl_wahl int not null,
    anzahl_auswertung int not null
)
    collate=latin1_german1_ci;

-- Table for all the courses, that can be assigned to elections
create table tbl_kurse
(
    id int auto_increment
        primary key,
    name text not null,
    beschreibung text not null,
    lehrer text not null,
    min int not null,
    max int not null,
    von datetime not null,
    bis datetime not null,
    sportwahl int not null,
    alias text not null
)
    collate=latin1_german1_ci;

-- Table to assign which user is allow to take part at which election
create table tbl_teilnehmer
(
    id int auto_increment
        primary key,
    benutzer int not null,
    wahl_typ int not null,
    wahl_id int not null
)
    collate=latin1_german1_ci;

-- Table to store the votes and results of all the users assigned to the election and
create table tbl_ergebnisse
(
    id int auto_increment
        primary key,
    sportwahl int not null,
    stimmnummer int not null,
    kurs int not null,
    benutzer int not null,
    akzeptiert tinyint(1) not null
)
    collate=latin1_german1_ci;




