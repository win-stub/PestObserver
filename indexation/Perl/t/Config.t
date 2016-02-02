use Test::More;
use File::Basename;
use File::Spec;
use JSON;

use Modules::Config;

{
  my $config = Modules::Config::Load(File::Spec->catfile(dirname(__FILE__), "xent_home"));
  is($config->{avoid}->{file}, 'avoid.txt');
}


{
  my $arrayj = decode_json('[{ "tag": "p", "file": "dico-p_v3.txt"}]');
  is($arrayj->[0]{tag}, "p");
  is(@$arrayj[0]->{tag}, "p");
  my $dicoMap = Modules::Config::MapDicoToTag($arrayj);


  is($dicoMap->{p}{file}, 'dico-p_v3.txt');
}

done_testing;

