## Intro
An adverse drug reaction (ADR) is an injury caused by taking a medication. Drug related morbidity and mortality was estimated  to be $177.4 billion and rising in the US. There are many research resources for researchers in this field, but they have never been together and as easily accessible until now.

## Inspiration

The drug safety market is huge (drug related morbidity and mortality was estimated to be $177.4 billion and rising in the US). Most people won't read all the box indications, the FDA website for adverse reports, or even simplified resources like drugs.com or drugbank, which often need some expert level knowledge to interpret. We aggregate research literature, FDA information, and publicly available data sets and resources to facilitate the advancement of the field.  

## What it does

Send small query, we will identify which drugs you are talking about and send you/provide you with a page that find all research literature involving the drug(s) and adverse effects. We even take this further and search a highly specialized curated resource that contains multiple leading drug interaction prediction methods and score the drug-drug association based on 6 different sources of information. All this work is publicly available and under publication in the leading journals.

## How I built it
Combining all my day job experience with the desire of hacking things together, this resource combines 6 different APIs and 6 different pharmacovigilance datasets to provide a comprehensive overview of drug interactions and adverse events.

## Challenges I ran into
Drug name normalization is a nightmare... I have spent months of my life addressing this problem in my day job.

## Accomplishments that I'm proud of
Combines multiple resources that have never been available in one single place via API calls, no need to install huge datasets locally. The level of expertise in the field to be able to put all these resources together has been a challenge in the last year of my life (after switching fields from solar informatics!!!), it all finally comes together in one nice and sleek product.

## What I learned
Tons of drug safety domain knowledge and technical know-how to combine resources in an efficient and clean way.

## What's next for Drug Safety Portal
Add more research data sources, improve API connectivity and full integration with bio2rdf resources
