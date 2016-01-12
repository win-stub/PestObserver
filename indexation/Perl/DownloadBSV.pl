use 5.018;
use autodie;
use warnings;
use File::Path qw(make_path);
use Getopt::Long 2.39;
use URI;

my $only = '.*';
my $verbose = 0;
my $targetdir = '.';
GetOptions('only=s' => \$only, 'to=s' => \$targetdir, 'verbose' => \$verbose) or die;

make_path($targetdir);
chdir $targetdir;

sub download {
  my ($regex, $source_url, @extra_options) = @_;

  return unless $source_url =~ /$only/;

  my $domain = URI->new($source_url)->host;

  my @args = ('--user-agent=BSV crawler for http://pestobserver.eu',
              '--timestamping',
              '--recursive',
              '--level=6',
              '--trust-server-names',
              '--domains='.$domain,
              '--accept-regex='.$regex,
              '--reject-regex=login');

  push @args, '--no-verbose' unless $verbose;

  push @args, @extra_options;

  say STDERR "Downloading BSV from: $source_url";

  my $ret = system 'wget', @args, $source_url;

  die '[ERROR] Failed to start wget' if $ret < 0;
  die sprintf('[ERROR] wget terminated with signal %s', $ret & 127) if $ret & 127;
  warn sprintf('[WARNING] wget exited with code %d', $ret >> 8) if $ret != 0;
}

my $default_regex = join('|', qw{
  \.pdf
  /bsv-
  ARCHIVES-BSV
  Arboriculture
  Archives-BSV
  BSV
  Bilan_
  [Bb]ulletin
  Campagne
  Chataigne
  Cultures-legumieres
  GRANDES-CULTURES
  Grandes-[Cc]ultures
  HORTICULTURE
  HOUBLON
  Horticulture
  LEGUMES
  Lin-oleagineux
  Maraichage
  Noix
  Nouvel-article
  Pepinieres
  Pour-les
  Prairie
  TABAC
  Tabac
  VIGNE
  Viticulture
  ZNA
  Zones-non-agricoles
});

download($default_regex, 'http://draaf.alsace-champagne-ardenne-lorraine.agriculture.gouv.fr/Surveillance-des-organismes');

download($default_regex, 'http://draaf.aquitaine-limousin-poitou-charentes.agriculture.gouv.fr/Bulletin-de-sante-du-vegetal');

download($default_regex
  . '|bulletins-de-sante-du-vegetal-bsv',
  'http://www.aquitainagri.fr/menu-horizontal/publications/bulletins-de-sante-du-vegetal-bsv.html');

download($default_regex
  . '|synagri/(arboriculture|pomme-de-terre|grandes-cultures'
  . '|fraise|maraichage-pomme-de-terre|prairies|chataigne|noix'
  . '|zone-non-agricole|tabac|viticulture|horticulture|legumes-de-plein-champ)|TECHPJPARCODE',
  'http://www.limousin.synagri.com/synagri/bulletins-de-sante-du-vegetal-00013060');

download($default_regex, 'http://draaf.auvergne-rhone-alpes.agriculture.gouv.fr/Bulletin-de-sante-du-vegetal');

download($default_regex, 'http://draaf.bourgogne-franche-comte.agriculture.gouv.fr/Bulletins-de-sante-du-vegetal-BSV');

download($default_regex
  . '|Legumes-frais|Legumes-industrie|Pommes-de-terre|Cultures-ornementales',
  'http://draaf.bretagne.agriculture.gouv.fr/Bulletin-de-Sante-du-Vegetal-BSV');

download($default_regex,
  'http://draaf.centre-val-de-loire.agriculture.gouv.fr/Bulletin-de-sante-du-vegetal');

download($default_regex,
  'http://draaf.corse.agriculture.gouv.fr/Les-bulletins-de-sante-du-vegetal');

download($default_regex
  . '|/20[0-9][0-9]|Midi-Pyrenees|Languedoc-Roussillon|Lozere',
  'http://draaf.languedoc-roussillon-midi-pyrenees.agriculture.gouv.fr/Bulletin-de-sante-du-vegetal');

download($default_regex
  . '|Nord-Pas-de-Calais|Picardie|Legumes|Petits-fruits',
  'http://draaf.nord-pas-de-calais-picardie.agriculture.gouv.fr/Bulletins-de-sante-du-vegetal-BSV');

download($default_regex
  . '|Campagne|Cereales|Horticulture|Legumes|Oleagineux|Lin',
  'http://draaf.normandie.agriculture.gouv.fr/Bulletin-de-sante-du-vegetal');

download($default_regex
  . '|/20[0-9][0-9]|Cultures-Mar|Oleiculture|Riz|Jardins|Vigne',
  'http://draaf.paca.agriculture.gouv.fr/Bulletin-de-sante-du-vegetal-BSV');

download($default_regex,
  'http://draaf.pays-de-la-loire.agriculture.gouv.fr/Derniers-BSV');
download($default_regex,
  'http://www.paysdelaloire.chambagri.fr/menu/vegetal/surveillance-biologique-du-territoire/bsv-grandes-cultures.html');

download($default_regex,
  'http://driaaf.ile-de-france.agriculture.gouv.fr/Epidemiosurveillance-et-Bulletin');
