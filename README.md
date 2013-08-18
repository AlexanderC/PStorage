# What is PStorage and why do we need this?
   The answer is pretty simple- it's the model layer for anything using flat files for storing data as it is an more complete engine.


# What is the main goal of the project?
   Before starting is tried to find something that will manage this, but nothing found.PStorage is more complex thing than simply an storage layer for the files. It give you an abstraction layer quite similar to the Doctrine Project with possibility to work with stored data in mode advanced way than usual done in such kind of engines.


# How does this work?
  The answer you can find in the "docs/main_scheme.txt" file. 

# Where ca i find examples?
 An example is located in the "tests/Post.php" file.


# ...and what about performance?
 For more details check "tests/performance.php" file.


### - TODO:


1.  Adding unit tests

2.  Improve binary tree overall performance and stability by splitting tree into many chunks
3.  Adding additional repair tools for database(currently trying to do it on demand)
4.  ... much more...
_P.S. Project needs more people working on to grow up..._