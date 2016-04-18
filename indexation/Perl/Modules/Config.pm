package Modules::Config;
use JSON;
use File::Spec;

sub Load
{
  my ($xent_home) = @_;

  my $json;
  {
    local $/; #Enable 'slurp' mode
    open my $fh, "<", File::Spec->catfile($xent_home, "www/config/ini.json");
    $json = <$fh>;
    close $fh;
  }

  decode_json($json);
}

sub MapDicoToTag
{
  my ($array) = @_;
  my %dicoMap = map { $_->{"tag"} => $_ } @$array;
  return \%dicoMap;
}

1;
