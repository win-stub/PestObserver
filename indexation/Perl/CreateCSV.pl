###############################################################################
#Created: PHAN TRONG TIEN                                                     #
#Created date: 20/03/2014													  #
###############################################################################
use Cwd 'abs_path';
my $dir =    abs_path($0) =~ s/CreateCSV\.pl//r;
#put library
push ( @INC,$dir);
#use modules defined
use Modules::Dico;
use Modules::Utils;
use Modules::Entite;
use Modules::Relation;
use Modules::Structure;
use Modules::Evaluation;
use Modules::Parametre;
use Modules::Config;
use File::Path qw(make_path);

use utf8;
use open(IO => ':encoding(utf8)');
binmode(STDERR, ':utf8');
binmode(STDOUT, ':utf8');
binmode(STDIN, ':utf8');# All output will be UTF-8

my $XENT_HOME = $ARGV[0];
my $XENT_DATA_DIR = File::Spec->catfile($XENT_HOME, "Perl/data");
my $DICO_TXT_DIR = File::Spec->catfile($XENT_HOME, "dico");
my $config = Modules::Config::Load($XENT_HOME);
my %DICO = ();
my %TYPE_TROUVE = ();
my %TAG_DICO = ();
#+++++++++++++++++++++++++++++++++++++++++++++++++
#lire les dictionnaire dans le fichier config
print "Attendez, S'il vous plaît ....................\n";
#si existant la configuration de "disco"
if (exists($config->{"dico"}) ){
	my @dico = @{$config->{dico}};
	if(scalar(@dico) > 0)
	{
		foreach(@dico)
		{
			my %info_dico = %{$_};
			my $tag = $info_dico{"tag"};
			my $file = File::Spec->catfile($DICO_TXT_DIR, $info_dico{"file"});
			my $node = $info_dico{"node"};
			my $col_key = $info_dico{"col_key"};
			my $col_val = $info_dico{"col_val"};
			my $filesize = -s $file;
			$filesize = $filesize/(1024*1024);
			#si la taille de fichier est supérieur 1MB, on vais trouver les mots dans le fichier dans le dictionnaire, et sinon en revanche
			if($filesize >= 1)
			{
				$TYPE_TROUVE{$file} = $Modules::Parametre::TROUVE_DIC; #gros data
			}else
			{
				$TYPE_TROUVE{$file} = $Modules::Parametre::TROUVE_CORPUS;
			}
			$TAG_DICO{$file} = $tag;
			my %sub_dico = Modules::Dico::LoadDico($file,$node,$col_key,$col_val);
			#Modules::Utils::PrintHashOfArray(%sub_dico);
			$DICO{$file} = \%sub_dico;
		}
	}
}
#créer le fichier csv unique (Id,Nom)
my $DIR_OUTPUT = File::Spec->catfile($XENT_DATA_DIR, "csv_temp");
make_path($DIR_OUTPUT);
for my $key (keys %TAG_DICO)
{
	my $f_name = ( split m{/}, $key )[-1];
	$f_name =~ s/\.txt/.csv/;
	my $FILE_OUTPUT = File::Spec->catfile($DIR_OUTPUT, $f_name);
	open(OUTPUT,'>:raw:encoding(UTF8)',$FILE_OUTPUT) || die "Can't open this file: $FILE_OUTPUT";
	my %dico = %{$DICO{$key}};
	my $ind = 0;
	my $data = ();#"Id;Name\n";
	for my $key_nom (keys %dico)
	{
		if(length($key_nom) > 0)
		{
			$ind += 1;
			$data .= $ind.";".$key_nom."\n";
		}
		
	}
	print OUTPUT $data;
	close(OUTPUT);
}
print "finish\n";
