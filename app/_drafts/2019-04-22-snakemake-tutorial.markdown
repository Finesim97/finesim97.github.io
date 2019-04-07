---
title:  "Snakemake Tutorial"
date:   2019-04-22 9:00:46 +0200
categories: Projects
tags: [Project Finish, Bioinformatics]
toc: true
---

Snakemake intro story, history of snakemake, personal use

## Conda & Snakemake Installation

## Running a workflow locally

## Running a workflow on a HPC Grid

## Exporting workflow information

## A simple workflow for paired read assembly

### Downloading the sample list (remote files)

### Reading the sample list in Snakemake (input functions)

### Downloading the samples (Conda, Threads)

### Running fastqc on each sample (expand function, parallel execution, localrules)

### Running PEAR to combine the reads (params, log, benchmark)

### Running fastqc on the merged reads (rule groups)

### Converting to FASTA (named pipes, tmp)

### Using Python/R/Rmarkdown Scripts (script)

## Other important points not in the example workflow (dir, shadow)
dir(), shadow

Something nice for you to check out:


## References
{% bibliography --cited %}
