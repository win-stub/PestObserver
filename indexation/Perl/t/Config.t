use Test::More tests => 1;
use File::Basename;
use File::Spec;

use Modules::Config;

my $config = Modules::Config::Load(File::Spec->catfile(dirname(__FILE__), "xent_home"));

is($config->{avoid}->{file}, 'avoid.txt');
