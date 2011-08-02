#!/usr/bin/perl
# by Jordan Tomkinson
# Were not fancy but were cheap!
$infile = "GeoIPCountryWhois.csv";
$outfile = "ip-to-country.csv";
open (FILE, "<$infile");
my @geoip = <FILE>;
close (FILE);

open (OUT, ">$outfile");
foreach my $line (@geoip) {
  chomp($line);
  my @array = split(',', $line);
  print OUT "\"\",".$array[2].",".$array[3].",".$array[4].",".$array[4].",".$array[5]."\n";
}
close (OUT);
