use 5.018;
use autodie;
use warnings;
use File::Path qw(make_path);
use Getopt::Long 2.39;
use File::Find;
use File::Copy;

my $from;
my $to;

GetOptions('to=s' => \$to, 'from=s' => \$from) or die;

die "$0: must specify --from and --to\n" unless ($from && $to);

my @files;
File::Find::find({
  wanted => sub { /\.pdf$/i && push @files, $_ },
  no_chdir => 1
}, $from);

foreach my $file (@files) {
  my $name = substr $file, ((length $from) + 1);

  # Replace every non-whitelisted character
  $name =~ s/[^-A-Za-z0-9_.]/_/g;

  say "$file => $to/$name";
  copy($file, "$to/$name");
}
