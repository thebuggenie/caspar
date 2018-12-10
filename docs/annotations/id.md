# Id column
b2db needs to know which field contains the unique identifier for the row, so it knows how to identify the different 
items. It does this using an id column. 

To specify the id column for an object, annotate the property that contains the id with an `@Id` annotation.

When storing new items b2db will automatically assign the id value to th`@Id` annotated property when the object is 
stored. 

**Note:** b2db does not support composite keys.
