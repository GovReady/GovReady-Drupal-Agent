import React, { PropTypes, Component } from 'react';
import { reduxForm, initialize, propTypes } from 'redux-form';
import DatePicker from 'react-datepicker';
import PureInput from 'components/PureInput';
import DeleteConfirm from 'components/DeleteConfirm';
export const fields = [
  '_id',
  'title',
  'description',
  'frequency',
  'startdate',
  'confirmDelete'
];
// Css
require('react-datepicker/dist/react-datepicker.css');

class MeasuresEditPage extends Component {

  editForm() {
    // Extract props
    const { fields: { 
      _id,
      title,
      description,
      frequency,
      startdate,
      confirmDelete 
    }, handleSubmit, measureSubmit, measureDelete, submitting } = this.props;
    const datePicker = () => {
      let selected = startdate.value 
                   ? window.moment(startdate.value, 'MMMM Do YYYY') 
                   : window.moment('MMMM Do YYYY');

      return (
        <DatePicker
          {...startdate}
          className='form-control'
          dateFormat="MMMM Do YYYY"
          selected={selected} />
      );
    }
    return (
      <form className="form-horizontal" onSubmit={handleSubmit(measureSubmit)}>
        <div className="row">
          <div className="col-md-12">
            <div className="form-group">
              <label className="col-sm-5 col-md-4 control-label">Title</label>
              <div className="col-sm-7 col-md-8">
                <PureInput type="text" field={title}/>
              </div>
            </div>
          </div>
          <div className="col-sm-6">
            <div className="form-group">
              <label className="col-sm-5 col-md-4 control-label">Frequency</label>
              <div className="col-sm-7 col-md-8">
                <PureInput type="email" field={frequency}/>
              </div>
            </div>
            <div className="form-group">
              <label className="col-sm-5 col-md-4 control-label">Start Date</label>
              <div className="col-sm-7 col-md-8">
                {datePicker()}
              </div>
            </div>
          </div>
        </div>
        <div>
          <button className="btn btn-primary" type="submit" disabled={submitting}>
            {submitting ? <i/> : <i/>} Submit
          </button>
          {_id && (
            <DeleteConfirm 
              index={_id} 
              confirmDelete={Boolean(confirmDelete.value)}
              deleteConfirm={confirmDelete.onChange}
              deleteFunc={() => { 
                measuresDelete(this.props.fields);
              }} />
          )}
          {this.props.backLink}
        </div>
      </form>
    )
  }

  render () {
    return (
      <div>
        {this.props.header}
        {this.editForm()}
      </div>
    );
  }
}

MeasuresEditPage.propTypes = {
  ...propTypes,
  header: PropTypes.object.isRequired,
  measureData: PropTypes.object.isRequired,
  emptyText: PropTypes.object.isRequired,
  measureSubmit: PropTypes.func.isRequired,
  measureDelete: PropTypes.func.isRequired,
  backLink: PropTypes.object.isRequired
};

export default reduxForm({
  form: 'measureEdit',
  fields
},
(state, ownProps) => ({
  initialValues: {
    ...ownProps.measureData
  }
})
)(MeasuresEditPage);