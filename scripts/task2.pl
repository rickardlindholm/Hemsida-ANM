#!/usr/bin/perl
#this is a perl file
#use strict;
#use warnings;
use Net::SNMP qw(:snmp);
#Use SNMP 
use DBI;
#Standard database interface
use DBD::mysql;
#DBD::mysql is an interface between the Perl programming language and the MySQL programming API that comes with the MySQL relational database management system.
require "task2.conf";
#Load config file
#PROBE VARIABLES
my $OID_In      = "1.3.6.1.2.1.2.2.1.10."; #MIB-address to incoming octets
my $OID_Out     = "1.3.6.1.2.1.2.2.1.16."; #MIB-address to outgoing octets
my $OID_SysUpTime       = "1.3.6.1.2.1.1.3.0"; #MIB-address to System up time
my $dsn = "dbi:mysql:$DATABASE:$HOST:$MYSQLPORT"; #Database from config file
###COUNTERS###
my $counter = 0;
###OCTET ARRAYS###
my @InOct;
my @OutOct;
###Array to send with DB Request###
my @ToSend;
###BitRate###
my $InBitrate;
my $OutBitrate;
###Arrays with AverageBitrate###
my @InAv;
my @OutAv;
###Device Information from DB#
my $Interface;
my $Community;
my $DeviceId;

###Connect to DB, if not able to connect provide error message###
my $dbh = DBI->connect($dsn, $USERNAME, $PASSWD) or die "Unable to connect: $DBI::errstr\n";
###Fetch Device information from DB, select###
my $sth = $dbh->prepare("SELECT id, IP, PORT, COMMUNITY, INTERFACES FROM $DEVICE_TABLE") or die "Couldn't fetch data from DB $dbh->errstr\n";

$sth->execute;

#Print the Device information (hash references) fetched from database
while (my $hash_ref = $sth->fetchrow_hashref)
{
#print "id: ", $hash_ref->{id}, " IP: ", $hash_ref->{IP}," PORT: " , $hash_ref->{PORT},
#" COMMUNITY: ", $hash_ref->{COMMUNITY}, " INTERFACES: ", $hash_ref->{INTERFACES}, "\n";

 
###Load Device information into new Variables###
$deviceId = $hash_ref->{id};
$Hostname = $hash_ref->{IP};
$Port = $hash_ref->{PORT};
$Community = $hash_ref->{COMMUNITY};
$Interface = $hash_ref->{INTERFACES};
###Start SNMP session### Translate = 0 -> Disables the translation mode for the object
my ($session, $error) = Net::SNMP->session(-hostname => $Hostname, -community => $Community, -port => $Port, -translate => 0); 

if (!defined $session) #If session is not defined provide error message
{
	printf	"Error: %s. \n", $error;
	exit;

}
###Separate the interfaces when encountering an empty string like: " " ###
my @ArrayInt = split (/ /,$Interface);
###Calculate the amount of Interfaces###
my $numscalar = scalar(@ArrayInt);
###Modify the MIB octets to the Interfaces###
$counter = 0;
foreach (@ArrayInt)
{
	$InOct[$counter] = $OID_In.($ArrayInt[$counter]);
	$OutOct[$counter] = $OID_Out.($ArrayInt[$counter]);
	$counter++;
}
###Prepare Array to send with OID for In Octets, Out Octets and System uptime###
@ToSend = (@InOct, @OutOct, $OID_SysUpTime);
###Is it really necessary to null the arrays###
my @CounterIn = (0) x $numscalar;
my @CounterOut = (0) x $numscalar;
my @PrvCounterIn = (0) x $numscalar;
my @PrvCounterOut = (0) x $numscalar;
my @AverageCounter = (0) x $numscalar;
my @AllInBit = (0) x $numscalar;
my @AllOutBit = (0) x $numscalar;
my $newTime = 0;
my $prvTime = 0;
###Fetch the SNMP Info and put it in result###
my      $result = $session->get_request(-varbindlist => \@ToSend);  
###Update the System up time###
$newTime = $result -> {$OID_SysUpTime};
$counter = 0;
foreach(@InOct)
{
	$CounterIn[$counter] = $result -> {$InOct[$counter]};
	$CounterOut[$counter] = $result -> {$OutOct[$counter]};
	$counter++;
}
###If Log table doesn't exist create it###
my $newsqltable = "CREATE TABLE IF NOT EXISTS $LOG_TABLE (
  `DeviceID` int(11) NOT NULL,
  `IP` varchar(25) NOT NULL,
  `Interface` text NOT NULL,
  `SysUpTime` int(11) NOT NULL,
  `FirstUpdateTime` timestamp NOT NULL DEFAULT '1970-01-01 01:01:01',
  `LastUpdateTime` timestamp NOT NULL DEFAULT '1970-01-01 01:01:01',
  `InCounter` int(11) NOT NULL,
  `OutCounter` int(11) NOT NULL,
  `TotalInCounter` int(11)  DEFAULT 0,
  `TotalOutCounter` int(11) DEFAULT 0,
  `Counter` int(11) NOT NULL,
  `AverageIn` int(11) DEFAULT 0,
  `AverageOut` int(11) DEFAULT 0,
  `InBitRate` int(11) DEFAULT 0,
  `OutBitRate` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
my $sth = $dbh->prepare($newsqltable);
$sth->execute() or die "SQL Error: $DBI::errstr\n";

$counter=0;
foreach (@ArrayInt)
{
	my $sth =  $dbh->prepare("Select * FROM Log_Table WHERE DeviceID = $deviceId AND Interface = $ArrayInt[$counter]"); 
	$sth->execute();
	my $data = $sth->fetchrow_hashref;
	if ($data->{SysUpTime}!=0)
	{
		###Fill variables from DB###
                $prvTime=$data->{SysUpTime};
                $PrvCounterIn[$counter]=$data->{InCounter};
                $PrvCounterOut[$counter]=$data->{OutCounter};
                $AllInBit[$counter]=$data->{TotalInCounter};
                $AllOutBit[$counter]=$data->{TotalOutCounter};
                $AverageCounter[$counter]=$data->{Counter};
                $AverageCounter[$counter]++;
                if ($PrvCounterIn[$counter] > $CounterIn[$counter])
                {                           
			###Calculate inBitrate when wrapping; 2 to the power of 32 = (32 bits)###
			$InBitrate=800*((2**32-$PrvCounterIn[$counter]+$CounterOut[$counter])/($newTime-$prvTime));
                }
                else
		{
			###Calculate inBitrate without wrapping###
			$InBitrate=800*(($CounterIn[$counter]-$PrvCounterIn[$counter])/($newTime-$prvTime));
                }
                if($PrvCounterOut[$counter] > $CounterOut[$counter])                     	
		{
			###Calculate outBitrate when wrapping###
			$OutBitrate=800*((2**32-$PrvCounterOut[$counter]+$CounterOut[$counter])/($newTime-$prvTime));
                }
		else
		{
			###Calculate outBitrate without wrapping###
			$OutBitrate=800*(($CounterOut[$counter]-$PrvCounterOut[$counter])/($newTime-$prvTime));
                }
		###All the bits added by the new bits divided by n###
		$InAverage[$counter]=($InBitrate+$AllInBit[$counter])/$AverageCounter[$counter];
		$OutAverage[$counter]=($OutBitrate+$AllOutBit[$counter])/$AverageCounter[$counter];
		###Store all the bits to enable counting the average###
		$AllInBit[$counter]=$AllInBit[$counter]+$InBitrate;
		$AllOutBit[$counter]=$AllOutBit[$counter]+$OutBitrate;
		###Update Log Table###
		my $updateTime = time();
		$sth = $dbh->prepare("UPDATE Log_Table SET `SysUpTime`=$newTime, `LastUpdateTime`=(FROM_UNIXTIME($updateTime)), `InCounter`=$CounterIn[$counter], `OutCounter`=$CounterOut[$counter], `TotalInCounter`=$AllInBit[$counter],
		`TotalOutCounter`=$AllOutBit[$counter], `Counter`=$AverageCounter[$counter],`AverageIn`=$InAverage[$counter],`AverageOut`=$OutAverage[$counter], `InBitRate`=$InBitrate,
		`OutBitRate`=$OutBitrate WHERE (`Interface` = $ArrayInt[$counter] AND `DeviceID` = $deviceId)");
		$sth->execute();
		$sth->finish();
	}
	else
	{
		###Insert new interfaces into the Log Table###		
		my $updateTime = time();
	        $sth = $dbh->prepare("INSERT INTO $LOG_TABLE (`Interface`,`SysUpTime`,`FirstUpdateTime`,`LastUpdateTime`,`InCounter`,`OutCounter`,`Counter`,`IP`,`DeviceID`) VALUES(?,?,FROM_UNIXTIME(?),FROM_UNIXTIME(?),?,?,?,?,?)");
                $sth->execute("$ArrayInt[$counter]", "$newTime", "$updateTime", "$updateTime", "$CounterIn[$counter]", "$CounterOut[$counter]", "$AverageCounter[$counter]", "$Hostname", "$deviceId");
                $sth->finish();
	}
$counter++;
}
}
$dbh->disconnect();
